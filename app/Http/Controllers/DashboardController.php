<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DatabaseEntity;
use App\Models\EmailAccount;
use App\Models\HostingAccount;
use App\Models\Server;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user && $user->isAdmin();

        if ($isAdmin) {
            $servers = Server::withCount(['accounts', 'accounts as active_accounts_count' => fn ($q) => $q->where('is_suspended', false)])->get();
            $accounts = HostingAccount::with(['server', 'user', 'emailAccounts', 'databases', 'domains'])->latest()->paginate(10);
            $recentLogs = ActivityLog::latest()->take(8)->get();

            $stats = [
                'total_servers' => $servers->count(),
                'active_servers' => $servers->where('status', 'active')->count(),
                'total_accounts' => HostingAccount::count(),
                'active_accounts' => HostingAccount::where('is_suspended', false)->count(),
                'suspended_accounts' => HostingAccount::where('is_suspended', true)->count(),
                'total_emails' => EmailAccount::count(),
                'total_databases' => DatabaseEntity::count(),
                'total_disk_used' => round(HostingAccount::sum('disk_used_mb') / 1024, 2),
                'total_disk_limit' => round(HostingAccount::sum('disk_limit_mb') / 1024, 2),
                'total_bw_used' => round(HostingAccount::sum('bandwidth_used_mb') / 1024, 2),
                'total_bw_limit' => round(HostingAccount::sum('bandwidth_limit_mb') / 1024, 2),
            ];
        } else {
            $servers = collect();
            $accounts = HostingAccount::with(['server', 'emailAccounts', 'databases', 'domains', 'cronJobs'])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(10);
            $recentLogs = ActivityLog::where('subject_id', (string) $user->id)->orWhereIn('subject_id', $accounts->pluck('id')->map(fn ($id) => (string) $id))->latest()->take(8)->get();

            $userAccountIds = $accounts->pluck('id');
            $stats = [
                'total_servers' => 1,
                'active_servers' => 1,
                'total_accounts' => HostingAccount::where('user_id', $user->id)->count(),
                'active_accounts' => HostingAccount::where('user_id', $user->id)->where('is_suspended', false)->count(),
                'suspended_accounts' => HostingAccount::where('user_id', $user->id)->where('is_suspended', true)->count(),
                'total_emails' => EmailAccount::whereIn('hosting_account_id', $userAccountIds)->count(),
                'total_databases' => DatabaseEntity::whereIn('hosting_account_id', $userAccountIds)->count(),
                'total_disk_used' => round(HostingAccount::where('user_id', $user->id)->sum('disk_used_mb') / 1024, 2),
                'total_disk_limit' => round(HostingAccount::where('user_id', $user->id)->sum('disk_limit_mb') / 1024, 2),
                'total_bw_used' => round(HostingAccount::where('user_id', $user->id)->sum('bandwidth_used_mb') / 1024, 2),
                'total_bw_limit' => round(HostingAccount::where('user_id', $user->id)->sum('bandwidth_limit_mb') / 1024, 2),
            ];
        }

        $allUsers = $isAdmin ? User::select('id', 'name', 'email', 'role')->get() : collect();

        return view('dashboard', compact('servers', 'accounts', 'recentLogs', 'stats', 'user', 'isAdmin', 'allUsers'));
    }
}
