<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\HostingAccount;
use App\Models\Server;
use App\Services\CPanel\CPanelApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    /**
     * List all servers
     */
    public function index(Request $request): JsonResponse
    {
        $query = Server::withCount([
            'accounts',
            'accounts as active_accounts_count' => function ($q) {
                $q->where('is_suspended', false);
            },
            'accounts as suspended_accounts_count' => function ($q) {
                $q->where('is_suspended', true);
            },
        ]);

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('hostname', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $servers = $query->latest()->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $servers,
        ]);
    }

    /**
     * Store a newly created server
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hostname' => 'required|string|max:255',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|between:1,65535',
            'protocol' => 'nullable|in:https,http',
            'username' => 'nullable|string|max:100',
            'api_token' => 'nullable|string',
            'password' => 'nullable|string',
            'auth_type' => 'nullable|in:api_token,password',
            'status' => 'nullable|in:active,offline,maintenance',
            'mock_mode' => 'nullable|boolean',
            'nameservers' => 'nullable|array',
            'max_accounts' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $server = Server::create($validated);

        ActivityLog::log(
            'server.create',
            "Added new cPanel/WHM server: {$server->name} ({$server->hostname})",
            'Server',
            (string) $server->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Server created successfully',
            'data' => $server,
        ], 201);
    }

    /**
     * Display server details
     */
    public function show(Server $server): JsonResponse
    {
        $server->loadCount(['accounts', 'accounts as active_accounts_count' => fn($q) => $q->where('is_suspended', false)]);
        $server->load(['accounts' => fn($q) => $q->latest()->take(10)]);

        return response()->json([
            'success' => true,
            'data' => $server,
        ]);
    }

    /**
     * Update server
     */
    public function update(Request $request, Server $server): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'hostname' => 'sometimes|required|string|max:255',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|between:1,65535',
            'protocol' => 'nullable|in:https,http',
            'username' => 'nullable|string|max:100',
            'api_token' => 'nullable|string',
            'password' => 'nullable|string',
            'auth_type' => 'nullable|in:api_token,password',
            'status' => 'nullable|in:active,offline,maintenance',
            'mock_mode' => 'nullable|boolean',
            'nameservers' => 'nullable|array',
            'max_accounts' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $server->update($validated);

        ActivityLog::log(
            'server.update',
            "Updated server: {$server->name}",
            'Server',
            (string) $server->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Server updated successfully',
            'data' => $server,
        ]);
    }

    /**
     * Remove server
     */
    public function destroy(Server $server): JsonResponse
    {
        $name = $server->name;
        $id = $server->id;
        $server->delete();

        ActivityLog::log(
            'server.delete',
            "Deleted server: {$name}",
            'Server',
            (string) $id
        );

        return response()->json([
            'success' => true,
            'message' => "Server '{$name}' deleted successfully",
        ]);
    }

    /**
     * Test connection to WHM/cPanel server
     */
    public function testConnection(Server $server): JsonResponse
    {
        $service = CPanelApiService::forServer($server);
        $result = $service->testConnection();

        return response()->json([
            'success' => $result['success'] ?? false,
            'data' => $result,
        ]);
    }

    /**
     * Sync accounts from remote WHM server to local database
     */
    public function syncAccounts(Server $server): JsonResponse
    {
        $service = CPanelApiService::forServer($server);
        $result = $service->listAccounts();

        $accountsData = $result['data']['acct'] ?? [];
        $syncedCount = 0;

        foreach ($accountsData as $acct) {
            $diskLimit = isset($acct['disklimit']) && is_numeric(rtrim($acct['disklimit'], 'M'))
                ? (int) rtrim($acct['disklimit'], 'M')
                : 5120;
            $diskUsed = isset($acct['diskused']) && is_numeric(rtrim($acct['diskused'], 'M'))
                ? (float) rtrim($acct['diskused'], 'M')
                : 0;

            HostingAccount::updateOrCreate(
                [
                    'server_id' => $server->id,
                    'username' => $acct['user'],
                ],
                [
                    'domain' => $acct['domain'],
                    'plan' => $acct['plan'] ?? 'Default',
                    'contact_email' => $acct['email'] ?? null,
                    'disk_limit_mb' => $diskLimit,
                    'disk_used_mb' => $diskUsed,
                    'ip_address' => $acct['ip'] ?? $server->ip_address,
                    'is_suspended' => (bool) ($acct['suspended'] ?? false),
                    'suspension_reason' => $acct['suspendreason'] ?? null,
                ]
            );

            $syncedCount++;
        }

        $server->update(['last_synced_at' => now()]);

        ActivityLog::log(
            'server.sync',
            "Synced {$syncedCount} accounts from server {$server->name}",
            'Server',
            (string) $server->id
        );

        return response()->json([
            'success' => true,
            'message' => "Synced {$syncedCount} accounts successfully.",
            'data' => [
                'synced_accounts' => $syncedCount,
                'last_synced_at' => $server->last_synced_at,
            ],
        ]);
    }

    /**
     * Fetch server hardware and software stats
     */
    public function stats(Server $server): JsonResponse
    {
        $service = CPanelApiService::forServer($server);
        $stats = $service->getServerStats();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
