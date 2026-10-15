<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\ServerController;
use App\Models\Server;
use App\Services\CPanel\CPanelApiService;
use Illuminate\Console\Command;

class PitoHubSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pitohub:sync-server {server_id? : ID of server to sync}';

    /**
     * The console command description.
     */
    protected $description = 'Sync accounts from cPanel/WHM server(s) into PitoHub database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $serverId = $this->argument('server_id');

        $servers = $serverId
            ? Server::where('id', $serverId)->get()
            : Server::where('status', 'active')->get();

        if ($servers->isEmpty()) {
            $this->warn('No active servers found to sync.');

            return Command::SUCCESS;
        }

        foreach ($servers as $server) {
            $this->info("Connecting to server: {$server->name} ({$server->hostname})...");
            $service = CPanelApiService::forServer($server);
            $test = $service->testConnection();

            if (! ($test['success'] ?? false)) {
                $this->error("Failed to connect to {$server->name}: ".($test['error'] ?? 'Unknown error'));

                continue;
            }

            $this->info('Connected successfully. Fetching accounts...');
            $serverController = app(ServerController::class);
            $response = $serverController->syncAccounts($server);
            $data = $response->getData(true);

            $this->line('Synced: '.($data['message'] ?? 'OK'));
        }

        $this->info('Server sync process completed.');

        return Command::SUCCESS;
    }
}
