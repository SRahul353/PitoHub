<?php

namespace Tests\Feature;

use App\Models\HostingAccount;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PitoHubApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $token;

    protected Server $server;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'admin@pitohub.local',
            'role' => 'admin',
        ]);

        $this->token = $this->user->createToken('Test Token')->plainTextToken;

        $this->server = Server::create([
            'name' => 'Test WHM Server',
            'hostname' => 'whm.test.pitohub.net',
            'ip_address' => '192.168.1.100',
            'port' => 2087,
            'protocol' => 'https',
            'username' => 'root',
            'api_token' => 'mock_token',
            'auth_type' => 'api_token',
            'status' => 'active',
            'mock_mode' => true,
        ]);
    }

    public function test_auth_login(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@pitohub.local',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['user', 'token', 'token_type'],
            ]);
    }

    public function test_list_servers(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/v1/servers');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Test WHM Server']);
    }

    public function test_server_test_connection(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson("/api/v1/servers/{$this->server->id}/test-connection");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_create_hosting_account(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/v1/accounts', [
                'server_id' => $this->server->id,
                'domain' => 'mybrand.com',
                'username' => 'mybrand',
                'password' => 'Secret12345!',
                'contact_email' => 'contact@mybrand.com',
                'plan' => 'Standard',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'domain' => 'mybrand.com',
                'username' => 'mybrand',
            ]);

        $this->assertDatabaseHas('hosting_accounts', [
            'username' => 'mybrand',
            'domain' => 'mybrand.com',
        ]);
    }

    public function test_suspend_and_unsuspend_account(): void
    {
        $account = HostingAccount::create([
            'server_id' => $this->server->id,
            'domain' => 'demo.org',
            'username' => 'demoorg',
            'is_suspended' => false,
        ]);

        // Suspend
        $suspendResponse = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson("/api/v1/accounts/{$account->id}/suspend", [
                'reason' => 'Billing dispute',
            ]);

        $suspendResponse->assertStatus(200);
        $this->assertTrue($account->fresh()->is_suspended);

        // Unsuspend
        $unsuspendResponse = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson("/api/v1/accounts/{$account->id}/unsuspend");

        $unsuspendResponse->assertStatus(200);
        $this->assertFalse($account->fresh()->is_suspended);
    }

    public function test_sso_url_generation(): void
    {
        $account = HostingAccount::create([
            'server_id' => $this->server->id,
            'domain' => 'sso-test.com',
            'username' => 'ssouser',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson("/api/v1/accounts/{$account->id}/sso-url");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_email_account_management(): void
    {
        $account = HostingAccount::create([
            'server_id' => $this->server->id,
            'domain' => 'mailtest.com',
            'username' => 'mailtest',
        ]);

        // Create email
        $createRes = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson("/api/v1/accounts/{$account->id}/emails", [
                'email' => 'hello@mailtest.com',
                'password' => 'Password123!',
                'quota_mb' => 2048,
            ]);

        $createRes->assertStatus(201)
            ->assertJsonFragment(['email' => 'hello@mailtest.com']);

        $emailId = $createRes->json('data.id');

        // Delete email
        $deleteRes = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson("/api/v1/accounts/{$account->id}/emails/{$emailId}");

        $deleteRes->assertStatus(200);
    }

    public function test_system_stats(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/v1/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'servers',
                    'accounts',
                    'resources',
                    'storage',
                    'bandwidth',
                ],
            ]);
    }
}
