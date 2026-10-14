<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\HostingAccount;
use App\Models\Server;
use App\Services\CPanel\CPanelApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HostingAccountController extends Controller
{
    /**
     * List hosting accounts
     */
    public function index(Request $request): JsonResponse
    {
        $query = HostingAccount::with('server:id,name,hostname,ip_address,status');

        if ($request->has('server_id')) {
            $query->where('server_id', $request->query('server_id'));
        }

        if ($request->has('is_suspended')) {
            $query->where('is_suspended', filter_var($request->query('is_suspended'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('domain', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }

        $accounts = $query->latest()->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $accounts,
        ]);
    }

    /**
     * Create account locally and on cPanel/WHM
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:servers,id',
            'domain' => 'required|string|max:255',
            'username' => 'required|string|alpha_num|max:16|unique:hosting_accounts,username',
            'password' => 'required|string|min:8',
            'contact_email' => 'nullable|email|max:255',
            'plan' => 'nullable|string|max:100',
            'disk_limit_mb' => 'nullable|integer|min:0',
            'bandwidth_limit_mb' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $server = Server::findOrFail($validated['server_id']);
        $service = CPanelApiService::forServer($server);

        // Provision on cPanel/WHM
        $whmResult = $service->createAccount($validated);

        $account = HostingAccount::create([
            'server_id' => $server->id,
            'domain' => strtolower($validated['domain']),
            'username' => strtolower($validated['username']),
            'contact_email' => $validated['contact_email'] ?? null,
            'plan' => $validated['plan'] ?? 'Standard',
            'disk_limit_mb' => $validated['disk_limit_mb'] ?? 5120,
            'bandwidth_limit_mb' => $validated['bandwidth_limit_mb'] ?? 51200,
            'ip_address' => $whmResult['data']['ip'] ?? $server->ip_address,
            'cpanel_created_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Create initial primary domain record
        $account->domains()->create([
            'domain_name' => $account->domain,
            'type' => 'main',
            'document_root' => 'public_html',
            'has_ssl' => false,
        ]);

        ActivityLog::log(
            'account.create',
            "Created cPanel account '{$account->username}' for domain {$account->domain} on {$server->name}",
            'HostingAccount',
            (string) $account->id
        );

        return response()->json([
            'success' => true,
            'message' => "Account '{$account->username}' created successfully.",
            'data' => $account->load(['server', 'domains']),
            'whm_response' => $whmResult,
        ], 201);
    }

    /**
     * Show account details
     */
    public function show(HostingAccount $account): JsonResponse
    {
        $account->load([
            'server',
            'emailAccounts',
            'databases',
            'domains',
            'cronJobs',
        ]);

        return response()->json([
            'success' => true,
            'data' => $account,
        ]);
    }

    /**
     * Update account info
     */
    public function update(Request $request, HostingAccount $account): JsonResponse
    {
        $validated = $request->validate([
            'contact_email' => 'nullable|email|max:255',
            'plan' => 'nullable|string|max:100',
            'disk_limit_mb' => 'nullable|integer|min:0',
            'bandwidth_limit_mb' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['plan']) && $validated['plan'] !== $account->plan) {
            $service = CPanelApiService::forServer($account->server);
            $service->changePackage($account->username, $validated['plan']);
        }

        $account->update($validated);

        ActivityLog::log(
            'account.update',
            "Updated account info for '{$account->username}'",
            'HostingAccount',
            (string) $account->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Account updated successfully',
            'data' => $account,
        ]);
    }

    /**
     * Terminate account
     */
    public function destroy(HostingAccount $account): JsonResponse
    {
        $username = $account->username;
        $id = $account->id;
        $server = $account->server;

        $service = CPanelApiService::forServer($server);
        $service->terminateAccount($username);

        $account->delete();

        ActivityLog::log(
            'account.terminate',
            "Terminated cPanel account '{$username}' from server {$server->name}",
            'HostingAccount',
            (string) $id
        );

        return response()->json([
            'success' => true,
            'message' => "Account '{$username}' terminated successfully.",
        ]);
    }

    /**
     * Suspend cPanel account
     */
    public function suspend(Request $request, HostingAccount $account): JsonResponse
    {
        $reason = $request->input('reason', 'Suspended via PitoHub Management');

        $service = CPanelApiService::forServer($account->server);
        $res = $service->suspendAccount($account->username, $reason);

        $account->update([
            'is_suspended' => true,
            'suspension_reason' => $reason,
            'suspended_at' => now(),
        ]);

        ActivityLog::log(
            'account.suspend',
            "Suspended account '{$account->username}'. Reason: {$reason}",
            'HostingAccount',
            (string) $account->id
        );

        return response()->json([
            'success' => true,
            'message' => "Account '{$account->username}' has been suspended.",
            'data' => $account,
            'whm_response' => $res,
        ]);
    }

    /**
     * Unsuspend cPanel account
     */
    public function unsuspend(HostingAccount $account): JsonResponse
    {
        $service = CPanelApiService::forServer($account->server);
        $res = $service->unsuspendAccount($account->username);

        $account->update([
            'is_suspended' => false,
            'suspension_reason' => null,
            'suspended_at' => null,
        ]);

        ActivityLog::log(
            'account.unsuspend',
            "Unsuspended account '{$account->username}'",
            'HostingAccount',
            (string) $account->id
        );

        return response()->json([
            'success' => true,
            'message' => "Account '{$account->username}' has been unsuspended.",
            'data' => $account,
            'whm_response' => $res,
        ]);
    }

    /**
     * Change cPanel password
     */
    public function changePassword(Request $request, HostingAccount $account): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $service = CPanelApiService::forServer($account->server);
        $res = $service->changePassword($account->username, $validated['password']);

        ActivityLog::log(
            'account.change_password',
            "Password changed for account '{$account->username}'",
            'HostingAccount',
            (string) $account->id
        );

        return response()->json([
            'success' => true,
            'message' => "Password changed successfully for account '{$account->username}'.",
            'whm_response' => $res,
        ]);
    }

    /**
     * Generate SSO 1-Click login URL to cPanel
     */
    public function ssoUrl(HostingAccount $account): JsonResponse
    {
        $service = CPanelApiService::forServer($account->server);
        $res = $service->createSessionUrl($account->username);

        return response()->json([
            'success' => isset($res['status']) && $res['status'] == 1,
            'data' => $res,
        ]);
    }
}
