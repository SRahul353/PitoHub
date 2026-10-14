<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\EmailAccount;
use App\Models\HostingAccount;
use App\Services\CPanel\CPanelApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailAccountController extends Controller
{
    /**
     * List email accounts for hosting account
     */
    public function index(HostingAccount $account): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $account->emailAccounts()->latest()->get(),
        ]);
    }

    /**
     * Create email account via cPanel UAPI Email::add_pop
     */
    public function store(Request $request, HostingAccount $account): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'quota_mb' => 'nullable|integer|min:0',
        ]);

        [$login, $domain] = explode('@', $validated['email'], 2);

        $service = CPanelApiService::forServer($account->server);
        $uapiResult = $service->uapiCall($account->username, 'Email', 'add_pop', [
            'email' => $login,
            'domain' => $domain,
            'password' => $validated['password'],
            'quota' => $validated['quota_mb'] ?? 1024,
        ]);

        $email = $account->emailAccounts()->create([
            'email' => strtolower($validated['email']),
            'login' => strtolower($login),
            'domain' => strtolower($domain),
            'quota_mb' => $validated['quota_mb'] ?? 1024,
            'disk_used_mb' => 0,
        ]);

        ActivityLog::log(
            'email.create',
            "Created email account {$email->email} for cPanel user {$account->username}",
            'EmailAccount',
            (string) $email->id
        );

        return response()->json([
            'success' => true,
            'message' => "Email account {$email->email} created successfully.",
            'data' => $email,
            'uapi_response' => $uapiResult,
        ], 201);
    }

    /**
     * Delete email account via cPanel UAPI Email::delete_pop
     */
    public function destroy(HostingAccount $account, EmailAccount $email): JsonResponse
    {
        if ($email->hosting_account_id !== $account->id) {
            return response()->json(['error' => 'Resource not found for this account'], 404);
        }

        $service = CPanelApiService::forServer($account->server);
        $service->uapiCall($account->username, 'Email', 'delete_pop', [
            'email' => $email->login,
            'domain' => $email->domain,
        ]);

        $address = $email->email;
        $email->delete();

        ActivityLog::log(
            'email.delete',
            "Deleted email account {$address} for user {$account->username}",
            'HostingAccount',
            (string) $account->id
        );

        return response()->json([
            'success' => true,
            'message' => "Email account {$address} deleted successfully.",
        ]);
    }
}
