<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PitoHub - cPanel Hosting Management & API Portal</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        code, pre { font-family: 'JetBrains Mono', monospace; }
        .glass { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">

    <!-- Top Navigation -->
    <header class="border-b border-slate-800 bg-slate-950/80 sticky top-0 z-40 backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 via-rose-500 to-indigo-600 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/20 text-lg">
                    P
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="font-extrabold text-xl tracking-tight text-white">PitoHub</span>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">cPanel & WHM Engine</span>
                    </div>
                    <p class="text-xs text-slate-400">Hosting Management Platform & REST API</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <span class="w-2 h-2 mr-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    API Active (v1)
                </span>
                <div class="flex items-center space-x-3">
                    <div class="text-xs text-slate-400 text-right hidden sm:block">
                        <div class="text-slate-200 font-medium flex items-center justify-end space-x-1.5">
                            <span>{{ auth()->user()->name ?? 'Administrator' }}</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase {{ (auth()->user()->role ?? 'admin') === 'admin' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-blue-500/20 text-blue-400 border border-blue-500/30' }}">
                                {{ auth()->user()->role ?? 'admin' }}
                            </span>
                        </div>
                        <div class="text-slate-500 font-mono text-[11px]">{{ auth()->user()->email ?? 'admin@pitohub.local' }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-rose-400 hover:text-rose-300 transition flex items-center space-x-1.5 shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <!-- Flash Message -->
        @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200">&times;</button>
        </div>
        @endif

        <!-- Top Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Servers Card -->
            <div class="p-5 rounded-2xl bg-slate-800/60 border border-slate-700/60 shadow-sm relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Connected Servers</p>
                        <h3 class="text-3xl font-extrabold text-white mt-1">{{ $stats['total_servers'] }}</h3>
                    </div>
                    <div class="p-2.5 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-slate-400">
                    <span class="text-emerald-400 font-semibold mr-1">{{ $stats['active_servers'] }} Active</span> &bull; 0 Offline
                </div>
            </div>

            <!-- Accounts Card -->
            <div class="p-5 rounded-2xl bg-slate-800/60 border border-slate-700/60 shadow-sm relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">cPanel Accounts</p>
                        <h3 class="text-3xl font-extrabold text-white mt-1">{{ $stats['total_accounts'] }}</h3>
                    </div>
                    <div class="p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-slate-400">
                    <span class="text-emerald-400 font-semibold mr-1">{{ $stats['active_accounts'] }} Normal</span> &bull;
                    <span class="text-amber-400 font-semibold ml-1">{{ $stats['suspended_accounts'] }} Suspended</span>
                </div>
            </div>

            <!-- Storage Card -->
            <div class="p-5 rounded-2xl bg-slate-800/60 border border-slate-700/60 shadow-sm relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Storage Used</p>
                        <h3 class="text-3xl font-extrabold text-white mt-1">{{ $stats['total_disk_used'] }} <span class="text-sm font-normal text-slate-400">GB</span></h3>
                    </div>
                    <div class="p-2.5 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7z"></path></svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="w-full bg-slate-700/60 h-1.5 rounded-full overflow-hidden">
                        @php $storagePercent = $stats['total_disk_limit'] > 0 ? ($stats['total_disk_used'] / $stats['total_disk_limit']) * 100 : 0; @endphp
                        <div class="bg-cyan-500 h-full rounded-full" style="width: {{ min(100, $storagePercent) }}%"></div>
                    </div>
                    <div class="mt-1 flex justify-between text-[11px] text-slate-400">
                        <span>{{ round($storagePercent, 1) }}% allocated</span>
                        <span>{{ $stats['total_disk_limit'] }} GB Limit</span>
                    </div>
                </div>
            </div>

            <!-- Managed Resources Card -->
            <div class="p-5 rounded-2xl bg-slate-800/60 border border-slate-700/60 shadow-sm relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Email & Databases</p>
                        <h3 class="text-3xl font-extrabold text-white mt-1">{{ $stats['total_emails'] + $stats['total_databases'] }}</h3>
                    </div>
                    <div class="p-2.5 rounded-xl bg-violet-500/10 border border-violet-500/20 text-violet-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-slate-400">
                    <span class="text-indigo-400 font-semibold mr-1">{{ $stats['total_emails'] }} Mailboxes</span> &bull;
                    <span class="text-violet-400 font-semibold ml-1">{{ $stats['total_databases'] }} MySQL DBs</span>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex flex-wrap gap-2 border-b border-slate-800 pb-3" id="tabButtons">
            <button onclick="switchTab('accountsTab')" class="tab-btn px-4 py-2 rounded-xl text-sm font-semibold transition bg-indigo-600 text-white shadow-md shadow-indigo-600/30" data-target="accountsTab">
                {{ $isAdmin ? 'cPanel Accounts (' . $accounts->total() . ')' : 'My Hosted Websites (' . $accounts->total() . ')' }}
            </button>
            @if($isAdmin)
            <button onclick="switchTab('serversTab')" class="tab-btn px-4 py-2 rounded-xl text-sm font-semibold transition bg-slate-800/80 hover:bg-slate-700 text-slate-300 flex items-center space-x-2" data-target="serversTab">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>cPanel API Config & Connections ({{ $servers->count() }})</span>
            </button>
            @endif
            <button onclick="switchTab('apiExplorerTab')" class="tab-btn px-4 py-2 rounded-xl text-sm font-semibold transition bg-slate-800/80 hover:bg-slate-700 text-slate-300" data-target="apiExplorerTab">
                REST API Explorer
            </button>
            @if($isAdmin)
            <button onclick="switchTab('logsTab')" class="tab-btn px-4 py-2 rounded-xl text-sm font-semibold transition bg-slate-800/80 hover:bg-slate-700 text-slate-300" data-target="logsTab">
                Activity Logs
            </button>
            @endif
        </div>

        <!-- TAB 1: ACCOUNTS -->
        <div id="accountsTab" class="tab-content space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-white">{{ $isAdmin ? 'Hosting Accounts' : 'Your Hosted Websites & Features' }}</h2>
                    <p class="text-sm text-slate-400">
                        {{ $isAdmin ? 'Manage cPanel instances, quotas, domains, SSO and suspensions' : 'Manage your domains, databases, emails, and cron jobs' }}
                    </p>
                </div>
                @if($isAdmin)
                <button onclick="document.getElementById('newAccountModal').classList.remove('hidden')" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 font-medium text-sm text-white shadow-lg shadow-indigo-600/20 flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Create cPanel Account</span>
                </button>
                @endif
            </div>

            <!-- Accounts Table -->
            <div class="rounded-2xl bg-slate-800/40 border border-slate-700/60 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="bg-slate-800/80 text-xs uppercase font-semibold text-slate-400 border-b border-slate-700/60">
                            <tr>
                                <th class="px-6 py-4">Domain / User</th>
                                <th class="px-6 py-4">Server</th>
                                <th class="px-6 py-4">Plan / Quota</th>
                                <th class="px-6 py-4">Storage Usage</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/40">
                            @forelse($accounts as $acc)
                            <tr class="hover:bg-slate-700/20 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-white flex items-center space-x-2">
                                        <span>{{ $acc->domain }}</span>
                                    </div>
                                    <div class="text-xs text-slate-400 mt-0.5 font-mono">
                                        user: <span class="text-indigo-400">{{ $acc->username }}</span>
                                        @if($acc->contact_email) &bull; {{ $acc->contact_email }} @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-semibold text-slate-200">{{ $acc->server->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $acc->server->hostname }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 text-xs rounded-md bg-slate-700 text-slate-200 font-medium">{{ $acc->plan }}</span>
                                    <div class="text-[11px] text-slate-400 mt-1">
                                        {{ $acc->email_accounts_count ?? $acc->emailAccounts->count() }} Emails &bull;
                                        {{ $acc->databases_count ?? $acc->databases->count() }} DBs
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2 text-xs">
                                        <span>{{ round($acc->disk_used_mb) }} MB</span>
                                        <span class="text-slate-500">/</span>
                                        <span class="text-slate-400">{{ $acc->disk_limit_mb }} MB</span>
                                    </div>
                                    <div class="w-28 bg-slate-700 h-1.5 rounded-full mt-1.5 overflow-hidden">
                                        <div class="bg-indigo-500 h-full rounded-full" style="width: {{ $acc->disk_usage_percentage }}%"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($acc->is_suspended)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            Suspended
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            Active
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                    <!-- 1-Click cPanel SSO Button -->
                                    <button onclick="fetchSsoUrl({{ $acc->id }}, '{{ $acc->username }}')" class="px-2.5 py-1 text-xs rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-medium inline-flex items-center space-x-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        <span>cPanel Login</span>
                                    </button>

                                    @if($isAdmin)
                                    <!-- Suspend/Unsuspend Button -->
                                    <form method="POST" action="{{ route('web.accounts.toggle-suspension', $acc->id) }}" class="inline-block">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 text-xs rounded-lg {{ $acc->is_suspended ? 'bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : 'bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border-amber-500/30' }} border font-medium">
                                            {{ $acc->is_suspended ? 'Unsuspend' : 'Suspend' }}
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                    No cPanel accounts found. Create one or sync from your server.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($accounts->hasPages())
                <div class="p-4 border-t border-slate-700/60 bg-slate-800/40">
                    {{ $accounts->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- TAB 2: SERVERS / CPANEL CONFIGURATION PANEL -->
        <div id="serversTab" class="tab-content hidden space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-white flex items-center space-x-2">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>cPanel & WHM Configuration Panel</span>
                    </h2>
                    <p class="text-sm text-slate-400">Configure connection credentials (UAPI / WHM), API tokens, ports, and verify live connectivity</p>
                </div>
                <button onclick="document.getElementById('newServerModal').classList.remove('hidden')" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 font-medium text-sm text-white shadow-lg shadow-indigo-600/20 flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Add / Connect cPanel or WHM</span>
                </button>
            </div>

            <!-- Configuration Helper Banner -->
            <div class="p-4 rounded-2xl bg-indigo-950/40 border border-indigo-500/20 text-xs text-slate-300 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <span class="font-bold text-indigo-300 block">Shared cPanel (Single Account Access):</span>
                    <p class="text-slate-400">Use Port <strong class="text-white">2083</strong>. In cPanel, navigate to <em>Security &rarr; Manage API Tokens</em> to generate your token. PitoHub executes direct UAPI commands for MySQL, Email, Subdomains & Cron.</p>
                </div>
                <div class="space-y-1">
                    <span class="font-bold text-amber-300 block">WHM Reseller / Root (Multi-Account Access):</span>
                    <p class="text-slate-400">Use Port <strong class="text-white">2087</strong>. Generate a WHM API Token under <em>Development &rarr; Manage API Tokens</em>. Allows creating accounts, suspension, package changes, and sync.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($servers as $s)
                <div class="p-6 rounded-2xl bg-slate-800/50 border border-slate-700/60 space-y-4 relative">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center space-x-2">
                                <h3 class="font-bold text-lg text-white">{{ $s->name }}</h3>
                                <span class="px-2 py-0.5 text-[11px] font-semibold rounded {{ ($s->server_type ?? 'cpanel_shared') === 'cpanel_shared' ? 'bg-sky-500/10 text-sky-400 border border-sky-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                                    {{ ($s->server_type ?? 'cpanel_shared') === 'cpanel_shared' ? 'cPanel Shared (2083)' : 'WHM Server (2087)' }}
                                </span>
                                @if($s->mock_mode)
                                    <span class="px-2 py-0.5 text-[11px] rounded bg-purple-500/10 text-purple-400 border border-purple-500/20">Simulation Mode</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $s->hostname }}:{{ $s->port }} (User: <span class="text-indigo-300 font-semibold">{{ $s->username }}</span>)</p>
                        </div>
                        <span class="px-2.5 py-1 text-xs rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">
                            {{ ucfirst($s->status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs bg-slate-900/40 p-3 rounded-xl border border-slate-800">
                        <div>
                            <span class="text-slate-500 block">Host / IP</span>
                            <span class="font-semibold text-slate-200 text-xs font-mono">{{ $s->ip_address ?? $s->hostname }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Auth Method</span>
                            <span class="font-semibold text-slate-200 text-xs uppercase">{{ $s->auth_type }} (Token Protected)</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Active Accounts</span>
                            <span class="font-semibold text-slate-200 text-sm">{{ $s->active_accounts_count }} accounts</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Last Synced</span>
                            <span class="text-slate-300">{{ $s->last_synced_at ? $s->last_synced_at->diffForHumans() : 'Never synced' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 gap-2 flex-wrap">
                        <div class="flex items-center space-x-2">
                            <!-- Test Connection AJAX -->
                            <button onclick="testServerConnection({{ $s->id }})" id="testBtn-{{ $s->id }}" class="px-3 py-1.5 text-xs rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium flex items-center space-x-1.5 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <span>Check Connection</span>
                            </button>

                            <!-- Edit Config Modal Trigger -->
                            <button onclick="openEditServerModal({{ json_encode($s) }})" class="px-3 py-1.5 text-xs rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-200 font-medium flex items-center space-x-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                <span>Configure</span>
                            </button>
                        </div>

                        <div class="flex items-center space-x-2">
                            @if(($s->server_type ?? 'cpanel_shared') === 'whm')
                            <!-- Sync Accounts -->
                            <form method="POST" action="{{ route('web.servers.sync', $s->id) }}">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 text-xs rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 font-medium flex items-center space-x-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    <span>Sync</span>
                                </button>
                            </form>
                            @endif

                            <!-- Delete Server -->
                            <form method="POST" action="{{ route('web.servers.delete', $s->id) }}" onsubmit="return confirm('Remove server config {{ $s->name }}?');">
                                @csrf
                                <button type="submit" class="p-1.5 text-xs rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20" title="Delete connection">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div id="testResult-{{ $s->id }}" class="hidden text-xs p-3 rounded-xl transition"></div>
                </div>
                @empty
                <div class="col-span-2 p-8 rounded-2xl bg-slate-800/40 border border-slate-700/60 text-center text-slate-400 space-y-3">
                    <p>No cPanel or WHM connections configured yet.</p>
                    <button onclick="document.getElementById('newServerModal').classList.remove('hidden')" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold">
                        Add Your First cPanel Connection
                    </button>
                </div>
                @endforelse
            </div>
        </div>

        <!-- TAB 3: API EXPLORER & DOCS -->
        <div id="apiExplorerTab" class="tab-content hidden space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-white">RESTful API Documentation & Explorer</h2>
                    <p class="text-sm text-slate-400">Complete API specification for external integration and client automation</p>
                </div>
                <div class="text-xs bg-slate-800 p-2.5 rounded-xl border border-slate-700 text-slate-300">
                    Base URL: <code class="text-indigo-400 font-bold">{{ url('/api/v1') }}</code>
                </div>
            </div>

            <!-- Authentication Banner -->
            <div class="p-5 rounded-2xl bg-gradient-to-r from-indigo-950/60 to-slate-900 border border-indigo-500/30 space-y-3">
                <div class="flex items-center space-x-2 text-indigo-400 font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    <span>Authentication Header</span>
                </div>
                <p class="text-xs text-slate-300">All protected endpoints require a Sanctum Bearer token in the <code class="text-indigo-300 font-mono">Authorization</code> header:</p>
                <div class="flex items-center justify-between bg-slate-950 p-3 rounded-xl border border-slate-800 font-mono text-xs text-emerald-400 overflow-x-auto">
                    <span>Authorization: Bearer {{ ($user ?? auth()->user())?->tokens()->latest()->first()?->token ? '2|Yq4GtvLOPDxwv6hW...' : '<YOUR_SANCTUM_TOKEN>' }}</span>
                    <button onclick="navigator.clipboard.writeText('{{ ($user ?? auth()->user())?->email ?? 'admin@pitohub.local' }} / password123'); alert('Credentials copied!');" class="text-xs text-slate-400 hover:text-white ml-4">Copy Auth</button>
                </div>
            </div>

            <!-- API Endpoints Accordion -->
            <div class="space-y-4">
                
                <!-- Group 1: Authentication -->
                <div class="rounded-xl bg-slate-800/40 border border-slate-700/60 p-4 space-y-3">
                    <h3 class="font-bold text-sm text-indigo-300 uppercase tracking-wider">1. Authentication</h3>
                    <div class="space-y-2">
                        <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-2">
                            <div class="flex items-center space-x-3">
                                <span class="px-2 py-0.5 text-xs font-bold rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">POST</span>
                                <code class="text-xs text-slate-200">/api/v1/auth/login</code>
                                <span class="text-xs text-slate-400">- Get token with email & password</span>
                            </div>
                            <button onclick="runApiSample('POST', '/api/v1/auth/login', {email: 'admin@pitohub.local', password: 'password123'})" class="px-3 py-1 text-xs rounded bg-indigo-600 hover:bg-indigo-500 text-white font-medium">Try Sample</button>
                        </div>
                        <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-2">
                            <div class="flex items-center space-x-3">
                                <span class="px-2 py-0.5 text-xs font-bold rounded bg-blue-500/20 text-blue-400 border border-blue-500/30">GET</span>
                                <code class="text-xs text-slate-200">/api/v1/auth/me</code>
                                <span class="text-xs text-slate-400">- Returns current authenticated user</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Group 2: WHM Servers -->
                <div class="rounded-xl bg-slate-800/40 border border-slate-700/60 p-4 space-y-3">
                    <h3 class="font-bold text-sm text-indigo-300 uppercase tracking-wider">2. Servers Management</h3>
                    <div class="space-y-2">
                        <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-800 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="px-2 py-0.5 text-xs font-bold rounded bg-blue-500/20 text-blue-400 border border-blue-500/30">GET</span>
                                <code class="text-xs text-slate-200">/api/v1/servers</code>
                                <span class="text-xs text-slate-400">- List servers with account counts</span>
                            </div>
                        </div>
                        <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-800 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="px-2 py-0.5 text-xs font-bold rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">POST</span>
                                <code class="text-xs text-slate-200">/api/v1/servers/{id}/test-connection</code>
                                <span class="text-xs text-slate-400">- Test WHM API connectivity</span>
                            </div>
                        </div>
                        <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-800 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="px-2 py-0.5 text-xs font-bold rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">POST</span>
                                <code class="text-xs text-slate-200">/api/v1/servers/{id}/sync-accounts</code>
                                <span class="text-xs text-slate-400">- Synchronize accounts from WHM</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Group 3: Hosting Accounts -->
                <div class="rounded-xl bg-slate-800/40 border border-slate-700/60 p-4 space-y-3">
                    <h3 class="font-bold text-sm text-indigo-300 uppercase tracking-wider">3. cPanel Accounts</h3>
                    <div class="space-y-2">
                        <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-800 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="px-2 py-0.5 text-xs font-bold rounded bg-blue-500/20 text-blue-400 border border-blue-500/30">GET</span>
                                <code class="text-xs text-slate-200">/api/v1/accounts</code>
                                <span class="text-xs text-slate-400">- Filter by server_id, search, suspension</span>
                            </div>
                        </div>
                        <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-800 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="px-2 py-0.5 text-xs font-bold rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">POST</span>
                                <code class="text-xs text-slate-200">/api/v1/accounts</code>
                                <span class="text-xs text-slate-400">- Provisions account on cPanel & saves locally</span>
                            </div>
                        </div>
                        <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-800 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="px-2 py-0.5 text-xs font-bold rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">POST</span>
                                <code class="text-xs text-slate-200">/api/v1/accounts/{id}/sso-url</code>
                                <span class="text-xs text-slate-400">- Generates direct magic login session</span>
                            </div>
                        </div>
                        <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-800 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="px-2 py-0.5 text-xs font-bold rounded bg-amber-500/20 text-amber-400 border border-amber-500/30">POST</span>
                                <code class="text-xs text-slate-200">/api/v1/accounts/{id}/suspend</code>
                                <span class="text-xs text-slate-400">- Suspend account with reason</span>
                            </div>
                        </div>
                        <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-800 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="px-2 py-0.5 text-xs font-bold rounded bg-rose-500/20 text-rose-400 border border-rose-500/30">DELETE</span>
                                <code class="text-xs text-slate-200">/api/v1/accounts/{id}</code>
                                <span class="text-xs text-slate-400">- Terminate from server and database</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Group 4: Sub-resources -->
                <div class="rounded-xl bg-slate-800/40 border border-slate-700/60 p-4 space-y-3">
                    <h3 class="font-bold text-sm text-indigo-300 uppercase tracking-wider">4. Sub-Resources (Emails, Databases, Domains, Cron)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                        <div class="p-2.5 bg-slate-900/60 rounded-lg border border-slate-800"><span class="text-blue-400 font-bold">GET</span> /api/v1/accounts/{id}/emails</div>
                        <div class="p-2.5 bg-slate-900/60 rounded-lg border border-slate-800"><span class="text-emerald-400 font-bold">POST</span> /api/v1/accounts/{id}/emails</div>
                        <div class="p-2.5 bg-slate-900/60 rounded-lg border border-slate-800"><span class="text-blue-400 font-bold">GET</span> /api/v1/accounts/{id}/databases</div>
                        <div class="p-2.5 bg-slate-900/60 rounded-lg border border-slate-800"><span class="text-emerald-400 font-bold">POST</span> /api/v1/accounts/{id}/databases</div>
                        <div class="p-2.5 bg-slate-900/60 rounded-lg border border-slate-800"><span class="text-blue-400 font-bold">GET</span> /api/v1/accounts/{id}/domains</div>
                        <div class="p-2.5 bg-slate-900/60 rounded-lg border border-slate-800"><span class="text-emerald-400 font-bold">POST</span> /api/v1/accounts/{id}/domains</div>
                        <div class="p-2.5 bg-slate-900/60 rounded-lg border border-slate-800"><span class="text-blue-400 font-bold">GET</span> /api/v1/accounts/{id}/cronjobs</div>
                        <div class="p-2.5 bg-slate-900/60 rounded-lg border border-slate-800"><span class="text-emerald-400 font-bold">POST</span> /api/v1/accounts/{id}/cronjobs</div>
                    </div>
                </div>

            </div>

            <!-- Live API Output Console -->
            <div id="apiConsoleWrapper" class="hidden space-y-2">
                <h4 class="text-xs font-bold uppercase text-slate-400 tracking-wider">Live API Response:</h4>
                <pre id="apiConsoleOutput" class="p-4 bg-slate-950 rounded-xl border border-slate-800 text-xs text-emerald-400 overflow-x-auto max-h-72"></pre>
            </div>
        </div>

        <!-- TAB 4: ACTIVITY LOGS -->
        <div id="logsTab" class="tab-content hidden space-y-6">
            <div>
                <h2 class="text-xl font-bold text-white">Audit & Activity Log</h2>
                <p class="text-sm text-slate-400">Chronological history of operations executed via Web UI and API</p>
            </div>

            <div class="rounded-2xl bg-slate-800/40 border border-slate-700/60 overflow-hidden">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-800/80 text-xs uppercase font-semibold text-slate-400 border-b border-slate-700/60">
                        <tr>
                            <th class="px-6 py-4">Action</th>
                            <th class="px-6 py-4">Description</th>
                            <th class="px-6 py-4">Target</th>
                            <th class="px-6 py-4">IP Address</th>
                            <th class="px-6 py-4">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/40 text-xs font-mono">
                        @forelse($recentLogs as $log)
                        <tr class="hover:bg-slate-700/20">
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-sans font-semibold">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-sans text-slate-200">{{ $log->description }}</td>
                            <td class="px-6 py-4 text-slate-400">{{ $log->target_type ? $log->target_type . ' #' . $log->target_id : '-' }}</td>
                            <td class="px-6 py-4 text-slate-400">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                            <td class="px-6 py-4 text-slate-400">{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400 font-sans">No activity logs recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Create cPanel Account Modal -->
    <div id="newAccountModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                <h3 class="text-lg font-bold text-white">Create New cPanel Account</h3>
                <button onclick="document.getElementById('newAccountModal').classList.add('hidden')" class="text-slate-400 hover:text-white text-xl">&times;</button>
            </div>
            <form method="POST" action="{{ route('web.accounts.store') }}" class="space-y-4 text-sm">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Target WHM Server *</label>
                    <select name="server_id" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                        @foreach($servers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->hostname }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Primary Domain *</label>
                        <input type="text" name="domain" placeholder="example.com" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">cPanel Username *</label>
                        <input type="text" name="username" placeholder="example" maxlength="16" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Hosting Plan *</label>
                        <input type="text" name="plan" value="Standard" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Storage Limit (MB)</label>
                        <input type="number" name="disk_limit_mb" value="5120" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Contact Email</label>
                        <input type="email" name="contact_email" placeholder="client@example.com" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Assign to User</label>
                        <select name="user_id" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                            <option value="">-- No User (Unassigned) --</option>
                            @foreach($allUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }}) [{{ $u->role }}]</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" onclick="document.getElementById('newAccountModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-medium hover:bg-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium shadow-md shadow-indigo-600/30">Create Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Connect New Server Modal -->
    <div id="newServerModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                <h3 class="text-lg font-bold text-white">Connect Server or cPanel Account</h3>
                <button onclick="document.getElementById('newServerModal').classList.add('hidden')" class="text-slate-400 hover:text-white text-xl">&times;</button>
            </div>
            <form method="POST" action="{{ route('web.servers.store') }}" class="space-y-4 text-sm">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Server Friendly Name *</label>
                        <input type="text" name="name" placeholder="e.g. My Shared cPanel" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Connection Type *</label>
                        <select name="server_type" onchange="document.getElementById('server_port').value = this.value === 'cpanel_shared' ? 2083 : 2087" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                            <option value="cpanel_shared" selected>Shared cPanel Account (Port 2083)</option>
                            <option value="whm">WHM Reseller / Root (Port 2087)</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-300 mb-1">Hostname or Domain *</label>
                        <input type="text" name="hostname" placeholder="cpanel.yourdomain.com" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Port</label>
                        <input type="number" name="port" id="server_port" value="2083" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Username (cPanel user or root) *</label>
                        <input type="text" name="username" placeholder="cpanel_username" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Server IP (Optional)</label>
                        <input type="text" name="ip_address" placeholder="192.0.2.1" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">API Token (Manage API Tokens in cPanel / WHM)</label>
                    <input type="password" name="api_token" placeholder="Paste cPanel or WHM API Token here" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                </div>
                <div class="p-3 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center space-x-2">
                    <input type="checkbox" name="mock_mode" id="mock_mode" value="1" checked class="rounded border-slate-700 bg-slate-800 text-indigo-600 focus:ring-0">
                    <label for="mock_mode" class="text-xs text-purple-300">
                        Enable Simulation Mode (Allows full local testing without real WHM credentials)
                    </label>
                </div>
                <div class="flex justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" onclick="document.getElementById('newServerModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-medium hover:bg-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium shadow-md shadow-indigo-600/30">Save & Connect</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit / Configure Server Modal -->
    <div id="editServerModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    <span>Edit cPanel / WHM Configuration</span>
                </h3>
                <button onclick="document.getElementById('editServerModal').classList.add('hidden')" class="text-slate-400 hover:text-white text-xl">&times;</button>
            </div>
            <form id="editServerForm" method="POST" action="" class="space-y-4 text-sm">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Friendly Name *</label>
                        <input type="text" name="name" id="edit_server_name" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Connection Type *</label>
                        <select name="server_type" id="edit_server_type" onchange="document.getElementById('edit_server_port').value = this.value === 'cpanel_shared' ? 2083 : 2087" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                            <option value="cpanel_shared">Shared cPanel (Port 2083)</option>
                            <option value="whm">WHM Reseller / Root (Port 2087)</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-300 mb-1">Hostname or Domain *</label>
                        <input type="text" name="hostname" id="edit_server_hostname" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Port</label>
                        <input type="number" name="port" id="edit_server_port" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">cPanel / WHM Username *</label>
                        <input type="text" name="username" id="edit_server_username" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Server IP (Optional)</label>
                        <input type="text" name="ip_address" id="edit_server_ip" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">New API Token (Leave empty to keep current)</label>
                    <input type="password" name="api_token" placeholder="Enter new API Token if updating" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white">
                </div>
                <div class="p-3 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center space-x-2">
                    <input type="checkbox" name="mock_mode" id="edit_server_mock" value="1" class="rounded border-slate-700 bg-slate-800 text-indigo-600 focus:ring-0">
                    <label for="edit_server_mock" class="text-xs text-purple-300">
                        Enable Simulation Mode (Local testing without real cPanel server)
                    </label>
                </div>
                <div class="flex justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" onclick="document.getElementById('editServerModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-medium hover:bg-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium shadow-md shadow-indigo-600/30">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Client Script -->
    <script>
        function openEditServerModal(server) {
            document.getElementById('editServerForm').action = `/web/servers/${server.id}/update`;
            document.getElementById('edit_server_name').value = server.name || '';
            document.getElementById('edit_server_type').value = server.server_type || 'cpanel_shared';
            document.getElementById('edit_server_hostname').value = server.hostname || '';
            document.getElementById('edit_server_port').value = server.port || (server.server_type === 'cpanel_shared' ? 2083 : 2087);
            document.getElementById('edit_server_username').value = server.username || '';
            document.getElementById('edit_server_ip').value = server.ip_address || '';
            document.getElementById('edit_server_mock').checked = Boolean(server.mock_mode);
            document.getElementById('editServerModal').classList.remove('hidden');
        }
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('bg-indigo-600', 'text-white', 'shadow-md', 'shadow-indigo-600/30');
                btn.classList.add('bg-slate-800/80', 'text-slate-300');
            });

            document.getElementById(tabId).classList.remove('hidden');
            const activeBtn = document.querySelector(`[data-target="${tabId}"]`);
            if (activeBtn) {
                activeBtn.classList.add('bg-indigo-600', 'text-white', 'shadow-md', 'shadow-indigo-600/30');
                activeBtn.classList.remove('bg-slate-800/80', 'text-slate-300');
            }
        }

        async function testServerConnection(serverId) {
            const btn = document.getElementById(`testBtn-${serverId}`);
            const resBox = document.getElementById(`testResult-${serverId}`);
            btn.disabled = true;
            btn.innerHTML = `<span class="animate-spin mr-1">⌛</span> Testing...`;

            try {
                // Call API directly
                const res = await fetch(`/api/v1/servers/${serverId}/test-connection`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();

                resBox.classList.remove('hidden');
                if (data.success) {
                    resBox.className = 'text-xs p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300';
                    resBox.innerText = `Connected: ${data.data.message || 'OK'} (Version: ${data.data.version || 'Active'})`;
                } else {
                    resBox.className = 'text-xs p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300';
                    resBox.innerText = `Connection Failed: ${data.data?.error || 'Unable to connect'}`;
                }
            } catch (err) {
                resBox.classList.remove('hidden');
                resBox.className = 'text-xs p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300';
                resBox.innerText = 'Network error while contacting server.';
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<svg class="w-3.5 h-3.5 text-indigo-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg> Test API Connection`;
            }
        }

        async function fetchSsoUrl(accountId, username) {
            try {
                const res = await fetch(`/api/v1/accounts/${accountId}/sso-url`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success && data.data?.url) {
                    window.open(data.data.url, '_blank');
                } else {
                    alert(`cPanel SSO URL for ${username}: ${data.data?.url || 'Simulated session URL generated'}`);
                }
            } catch (err) {
                alert(`Error creating cPanel SSO session for ${username}`);
            }
        }

        async function runApiSample(method, url, body) {
            const wrapper = document.getElementById('apiConsoleWrapper');
            const output = document.getElementById('apiConsoleOutput');
            wrapper.classList.remove('hidden');
            output.innerText = `Sending ${method} request to ${url}...`;

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: body ? JSON.stringify(body) : undefined
                });
                const data = await res.json();
                output.innerText = JSON.stringify(data, null, 2);
            } catch (err) {
                output.innerText = `Error: ${err.message}`;
            }
        }
    </script>
</body>
</html>
