<?php

namespace Tests\Feature;

use App\Models\HostingAccount;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $clientUser;

    protected Server $server;

    protected HostingAccount $clientAccount;

    protected HostingAccount $otherAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@pitohub.local',
            'role' => 'admin',
        ]);

        $this->clientUser = User::factory()->create([
            'email' => 'client@pitohub.local',
            'role' => 'user',
        ]);

        $this->server = Server::create([
            'name' => 'Shared cPanel Server',
            'server_type' => 'cpanel_shared',
            'hostname' => 'shared.pitohub.net',
            'port' => 2083,
            'protocol' => 'https',
            'username' => 'cpaneluser',
            'api_token' => 'test_api_token',
            'auth_type' => 'api_token',
            'status' => 'active',
            'mock_mode' => true,
        ]);

        $this->clientAccount = HostingAccount::create([
            'server_id' => $this->server->id,
            'user_id' => $this->clientUser->id,
            'domain' => 'clientdomain.com',
            'username' => 'clientuser',
            'plan' => 'Standard',
            'disk_limit_mb' => 2048,
            'bandwidth_limit_mb' => 20480,
            'cpanel_created_at' => now(),
        ]);

        $this->otherAccount = HostingAccount::create([
            'server_id' => $this->server->id,
            'user_id' => null, // Or owned by someone else
            'domain' => 'otherdomain.com',
            'username' => 'otheruser',
            'plan' => 'Standard',
            'disk_limit_mb' => 2048,
            'bandwidth_limit_mb' => 20480,
            'cpanel_created_at' => now(),
        ]);
    }

    public function test_regular_user_cannot_access_servers_api(): void
    {
        $token = $this->clientUser->createToken('ClientToken')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/servers');

        $response->assertStatus(403);
    }

    public function test_regular_user_only_sees_their_own_accounts(): void
    {
        $token = $this->clientUser->createToken('ClientToken')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/accounts');

        $response->assertStatus(200);
        $data = $response->json('data.data');

        $this->assertCount(1, $data);
        $this->assertEquals('clientdomain.com', $data[0]['domain']);
    }

    public function test_regular_user_cannot_view_or_modify_other_accounts(): void
    {
        $token = $this->clientUser->createToken('ClientToken')->plainTextToken;

        // View other account
        $viewRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/accounts/'.$this->otherAccount->id);
        $viewRes->assertStatus(403);

        // Cannot suspend
        $suspendRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/accounts/'.$this->clientAccount->id.'/suspend');
        $suspendRes->assertStatus(403);
    }

    public function test_regular_user_can_manage_their_own_databases_and_emails(): void
    {
        $token = $this->clientUser->createToken('ClientToken')->plainTextToken;

        // Create Database
        $dbRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/accounts/{$this->clientAccount->id}/databases", [
                'database_name' => 'blogdb',
            ]);
        $dbRes->assertStatus(201);

        // Create Email Account
        $emailRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/accounts/{$this->clientAccount->id}/emails", [
                'email' => 'support@clientdomain.com',
                'password' => 'secret_pass_123',
            ]);
        $emailRes->assertStatus(201);
    }

    public function test_admin_has_full_access(): void
    {
        $token = $this->admin->createToken('AdminToken')->plainTextToken;

        // Can list servers
        $serversRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/servers');
        $serversRes->assertStatus(200);

        // Can view all accounts
        $accountsRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/accounts');
        $accountsRes->assertStatus(200);
        $this->assertCount(2, $accountsRes->json('data.data'));
    }
}
