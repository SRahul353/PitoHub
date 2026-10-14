<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\CronJobEntity;
use App\Models\DatabaseEntity;
use App\Models\DomainEntity;
use App\Models\EmailAccount;
use App\Models\HostingAccount;
use App\Models\Server;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@pitohub.local'],
            [
                'name' => 'PitoHub Admin',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Pre-generate a Sanctum token
        $admin->tokens()->delete();
        $admin->createToken('Default Admin Token', ['*']);

        // 2. Create Sample WHM/cPanel Servers
        $server1 = Server::updateOrCreate(
            ['hostname' => 'whm1.us-east.pitohub.net'],
            [
                'name' => 'US-East Production WHM',
                'ip_address' => '198.51.100.24',
                'port' => 2087,
                'protocol' => 'https',
                'username' => 'root',
                'api_token' => 'WHM_SECRET_TOKEN_EXAMPLE_US_EAST_9901',
                'auth_type' => 'api_token',
                'status' => 'active',
                'mock_mode' => true, // Enables full simulated responses out-of-the-box
                'nameservers' => ['ns1.pitohub.net', 'ns2.pitohub.net'],
                'max_accounts' => 150,
                'last_synced_at' => now()->subMinutes(12),
                'notes' => 'Primary high-performance AMD EPYC server hosting North America accounts.',
            ]
        );

        $server2 = Server::updateOrCreate(
            ['hostname' => 'whm2.eu-central.pitohub.net'],
            [
                'name' => 'EU-Central Backup Node',
                'ip_address' => '203.0.113.88',
                'port' => 2087,
                'protocol' => 'https',
                'username' => 'root',
                'api_token' => 'WHM_SECRET_TOKEN_EXAMPLE_EU_CENTRAL_4421',
                'auth_type' => 'api_token',
                'status' => 'active',
                'mock_mode' => true,
                'nameservers' => ['ns3.pitohub.net', 'ns4.pitohub.net'],
                'max_accounts' => 100,
                'last_synced_at' => now()->subHours(2),
                'notes' => 'Frankfurt node for European clients and backup redundancy.',
            ]
        );

        // 3. Create Sample Hosting Accounts
        $acc1 = HostingAccount::updateOrCreate(
            ['username' => 'novacloud'],
            [
                'server_id' => $server1->id,
                'domain' => 'novacloud.io',
                'contact_email' => 'admin@novacloud.io',
                'plan' => 'Pro Cloud',
                'disk_limit_mb' => 10240,
                'disk_used_mb' => 2480.50,
                'bandwidth_limit_mb' => 102400,
                'bandwidth_used_mb' => 34210.80,
                'ip_address' => '198.51.100.24',
                'is_suspended' => false,
                'cpanel_created_at' => now()->subMonths(3),
                'notes' => 'SaaS customer platform on Laravel stack.',
            ]
        );

        $acc2 = HostingAccount::updateOrCreate(
            ['username' => 'zenithsh'],
            [
                'server_id' => $server1->id,
                'domain' => 'zenithshop.net',
                'contact_email' => 'operations@zenithshop.net',
                'plan' => 'E-Commerce Plus',
                'disk_limit_mb' => 20480,
                'disk_used_mb' => 7890.20,
                'bandwidth_limit_mb' => 204800,
                'bandwidth_used_mb' => 84120.00,
                'ip_address' => '198.51.100.24',
                'is_suspended' => false,
                'cpanel_created_at' => now()->subMonths(1),
                'notes' => 'Online retail store with WooCommerce/cPanel setup.',
            ]
        );

        $acc3 = HostingAccount::updateOrCreate(
            ['username' => 'alphalog'],
            [
                'server_id' => $server2->id,
                'domain' => 'alphalogistics.com',
                'contact_email' => 'it@alphalogistics.com',
                'plan' => 'Corporate Tier',
                'disk_limit_mb' => 15360,
                'disk_used_mb' => 4120.00,
                'bandwidth_limit_mb' => 153600,
                'bandwidth_used_mb' => 23100.40,
                'ip_address' => '203.0.113.88',
                'is_suspended' => false,
                'cpanel_created_at' => now()->subWeeks(2),
                'notes' => 'Logistics tracking frontend and backend portal.',
            ]
        );

        $acc4 = HostingAccount::updateOrCreate(
            ['username' => 'oldcorp'],
            [
                'server_id' => $server2->id,
                'domain' => 'oldcorp-demo.org',
                'contact_email' => 'billing@oldcorp-demo.org',
                'plan' => 'Starter',
                'disk_limit_mb' => 5120,
                'disk_used_mb' => 3890.00,
                'bandwidth_limit_mb' => 51200,
                'bandwidth_used_mb' => 4500.00,
                'ip_address' => '203.0.113.88',
                'is_suspended' => true,
                'suspension_reason' => 'Overdue invoice #INV-2026-9041 (60 days overdue)',
                'suspended_at' => now()->subDays(5),
                'cpanel_created_at' => now()->subMonths(6),
                'notes' => 'Awaiting payment confirmation before unsuspending.',
            ]
        );

        // 4. Sample Email Accounts
        EmailAccount::firstOrCreate([
            'hosting_account_id' => $acc1->id,
            'email' => 'admin@novacloud.io',
        ], [
            'login' => 'admin',
            'domain' => 'novacloud.io',
            'quota_mb' => 2048,
            'disk_used_mb' => 420.5,
        ]);

        EmailAccount::firstOrCreate([
            'hosting_account_id' => $acc1->id,
            'email' => 'support@novacloud.io',
        ], [
            'login' => 'support',
            'domain' => 'novacloud.io',
            'quota_mb' => 5120,
            'disk_used_mb' => 1250.0,
        ]);

        EmailAccount::firstOrCreate([
            'hosting_account_id' => $acc2->id,
            'email' => 'orders@zenithshop.net',
        ], [
            'login' => 'orders',
            'domain' => 'zenithshop.net',
            'quota_mb' => 4096,
            'disk_used_mb' => 890.3,
        ]);

        // 5. Sample Databases
        DatabaseEntity::firstOrCreate([
            'hosting_account_id' => $acc1->id,
            'database_name' => 'novacloud_prod',
        ], [
            'db_user' => 'novacloud_dbuser',
            'size_mb' => 340.2,
        ]);

        DatabaseEntity::firstOrCreate([
            'hosting_account_id' => $acc2->id,
            'database_name' => 'zenithsh_store',
        ], [
            'db_user' => 'zenithsh_storeuser',
            'size_mb' => 1250.8,
        ]);

        // 6. Sample Domains
        DomainEntity::firstOrCreate([
            'hosting_account_id' => $acc1->id,
            'domain_name' => 'novacloud.io',
        ], [
            'type' => 'main',
            'document_root' => 'public_html',
            'has_ssl' => true,
        ]);

        DomainEntity::firstOrCreate([
            'hosting_account_id' => $acc1->id,
            'domain_name' => 'api.novacloud.io',
        ], [
            'type' => 'subdomain',
            'document_root' => 'public_html/api',
            'has_ssl' => true,
        ]);

        DomainEntity::firstOrCreate([
            'hosting_account_id' => $acc2->id,
            'domain_name' => 'zenithshop.net',
        ], [
            'type' => 'main',
            'document_root' => 'public_html',
            'has_ssl' => true,
        ]);

        // 7. Sample Cron Jobs
        CronJobEntity::firstOrCreate([
            'hosting_account_id' => $acc1->id,
            'command' => '/usr/local/bin/php /home/novacloud/artisan schedule:run >> /dev/null 2>&1',
        ], [
            'schedule' => '* * * * *',
            'description' => 'Laravel Scheduler',
            'is_active' => true,
        ]);

        CronJobEntity::firstOrCreate([
            'hosting_account_id' => $acc2->id,
            'command' => '/usr/bin/php /home/zenithsh/cron_sync_inventory.php',
        ], [
            'schedule' => '0 */2 * * *',
            'description' => 'Inventory Sync every 2 hours',
            'is_active' => true,
        ]);

        // 8. Sample Activity Logs
        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'server.sync',
            'description' => "Synced accounts for US-East Production WHM",
            'target_type' => 'Server',
            'target_id' => (string) $server1->id,
            'ip_address' => '127.0.0.1',
        ]);

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'account.create',
            'description' => "Created cPanel account 'novacloud' for domain novacloud.io",
            'target_type' => 'HostingAccount',
            'target_id' => (string) $acc1->id,
            'ip_address' => '127.0.0.1',
        ]);

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'account.suspend',
            'description' => "Suspended account 'oldcorp' due to overdue payment",
            'target_type' => 'HostingAccount',
            'target_id' => (string) $acc4->id,
            'ip_address' => '127.0.0.1',
        ]);
    }
}
