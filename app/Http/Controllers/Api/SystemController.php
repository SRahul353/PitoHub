<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DatabaseEntity;
use App\Models\EmailAccount;
use App\Models\HostingAccount;
use App\Models\Server;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    /**
     * Return high-level aggregated statistics
     */
    public function overviewStats(): JsonResponse
    {
        $totalServers = Server::count();
        $activeServers = Server::where('status', 'active')->count();
        $totalAccounts = HostingAccount::count();
        $activeAccounts = HostingAccount::where('is_suspended', false)->count();
        $suspendedAccounts = HostingAccount::where('is_suspended', true)->count();
        $totalEmails = EmailAccount::count();
        $totalDatabases = DatabaseEntity::count();

        $totalDiskUsed = HostingAccount::sum('disk_used_mb');
        $totalDiskLimit = HostingAccount::sum('disk_limit_mb');
        $totalBandwidthUsed = HostingAccount::sum('bandwidth_used_mb');
        $totalBandwidthLimit = HostingAccount::sum('bandwidth_limit_mb');

        return response()->json([
            'success' => true,
            'data' => [
                'servers' => [
                    'total' => $totalServers,
                    'active' => $activeServers,
                    'offline' => Server::where('status', 'offline')->count(),
                    'maintenance' => Server::where('status', 'maintenance')->count(),
                ],
                'accounts' => [
                    'total' => $totalAccounts,
                    'active' => $activeAccounts,
                    'suspended' => $suspendedAccounts,
                ],
                'resources' => [
                    'emails' => $totalEmails,
                    'databases' => $totalDatabases,
                ],
                'storage' => [
                    'used_mb' => round($totalDiskUsed, 2),
                    'limit_mb' => $totalDiskLimit,
                    'percentage' => $totalDiskLimit > 0 ? round(($totalDiskUsed / $totalDiskLimit) * 100, 1) : 0,
                ],
                'bandwidth' => [
                    'used_mb' => round($totalBandwidthUsed, 2),
                    'limit_mb' => $totalBandwidthLimit,
                    'percentage' => $totalBandwidthLimit > 0 ? round(($totalBandwidthUsed / $totalBandwidthLimit) * 100, 1) : 0,
                ],
            ],
        ]);
    }

    /**
     * Return recent activity logs
     */
    public function activityLogs(Request $request): JsonResponse
    {
        $logs = ActivityLog::with('user:id,name,email')
            ->latest()
            ->paginate($request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}
