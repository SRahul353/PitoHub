<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CronJobEntity;
use App\Models\HostingAccount;
use App\Services\CPanel\CPanelApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CronJobEntityController extends Controller
{
    /**
     * List cron jobs
     */
    public function index(HostingAccount $account): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $account->cronJobs()->latest()->get(),
        ]);
    }

    /**
     * Add cron job via cPanel UAPI Cron::add_line
     */
    public function store(Request $request, HostingAccount $account): JsonResponse
    {
        $validated = $request->validate([
            'schedule' => 'required|string|max:100', // e.g. "0 * * * *"
            'command' => 'required|string',
            'description' => 'nullable|string|max:255',
        ]);

        $service = CPanelApiService::forServer($account->server);
        $res = $service->uapiCall($account->username, 'Cron', 'add_line', [
            'command' => $validated['command'],
            'schedule' => $validated['schedule'],
        ]);

        $cron = $account->cronJobs()->create([
            'schedule' => $validated['schedule'],
            'command' => $validated['command'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        ActivityLog::log(
            'cron.create',
            "Added cron job '{$cron->schedule} {$cron->command}' for {$account->username}",
            'CronJobEntity',
            (string) $cron->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Cron job created successfully.',
            'data' => $cron,
            'uapi_response' => $res,
        ], 201);
    }

    /**
     * Remove cron job
     */
    public function destroy(HostingAccount $account, CronJobEntity $cronjob): JsonResponse
    {
        if ($cronjob->hosting_account_id !== $account->id) {
            return response()->json(['error' => 'Resource not found for this account'], 404);
        }

        $cronjob->delete();

        ActivityLog::log(
            'cron.delete',
            "Removed cron job #{$cronjob->id} for {$account->username}",
            'HostingAccount',
            (string) $account->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Cron job removed successfully.',
        ]);
    }
}
