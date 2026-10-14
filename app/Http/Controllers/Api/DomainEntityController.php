<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DomainEntity;
use App\Models\HostingAccount;
use App\Services\CPanel\CPanelApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DomainEntityController extends Controller
{
    /**
     * List domains
     */
    public function index(HostingAccount $account): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $account->domains()->latest()->get(),
        ]);
    }

    /**
     * Add addon or subdomain via cPanel UAPI
     */
    public function store(Request $request, HostingAccount $account): JsonResponse
    {
        $validated = $request->validate([
            'domain_name' => 'required|string|max:255',
            'type' => 'required|in:addon,subdomain,alias',
            'document_root' => 'nullable|string|max:255',
        ]);

        $docRoot = $validated['document_root'] ?? 'public_html/' . explode('.', $validated['domain_name'])[0];

        $service = CPanelApiService::forServer($account->server);

        if ($validated['type'] === 'subdomain') {
            $parts = explode('.', $validated['domain_name']);
            $sub = $parts[0];
            $rootDomain = implode('.', array_slice($parts, 1));

            $uapiRes = $service->uapiCall($account->username, 'SubDomain', 'addsubdomain', [
                'domain' => $sub,
                'rootdomain' => $rootDomain ?: $account->domain,
                'dir' => $docRoot,
            ]);
        } else {
            // Addon or Alias
            $uapiRes = $service->uapiCall($account->username, 'DomainLookup', 'get_basedata', [
                'domain' => $validated['domain_name'],
            ]);
        }

        $domain = $account->domains()->create([
            'domain_name' => strtolower($validated['domain_name']),
            'type' => $validated['type'],
            'document_root' => $docRoot,
            'has_ssl' => false,
        ]);

        ActivityLog::log(
            'domain.create',
            "Added {$domain->type} domain {$domain->domain_name} for {$account->username}",
            'DomainEntity',
            (string) $domain->id
        );

        return response()->json([
            'success' => true,
            'message' => "Domain {$domain->domain_name} added successfully.",
            'data' => $domain,
            'uapi_response' => $uapiRes ?? null,
        ], 201);
    }

    /**
     * Remove domain
     */
    public function destroy(HostingAccount $account, DomainEntity $domain): JsonResponse
    {
        if ($domain->hosting_account_id !== $account->id) {
            return response()->json(['error' => 'Resource not found for this account'], 404);
        }

        if ($domain->type === 'main') {
            return response()->json([
                'success' => false,
                'message' => 'Primary domain cannot be removed directly. Please change it via account settings.',
            ], 422);
        }

        $name = $domain->domain_name;
        $domain->delete();

        ActivityLog::log(
            'domain.delete',
            "Removed domain {$name} from account {$account->username}",
            'HostingAccount',
            (string) $account->id
        );

        return response()->json([
            'success' => true,
            'message' => "Domain {$name} removed successfully.",
        ]);
    }
}
