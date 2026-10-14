<?php

use App\Http\Controllers\DashboardController;
use App\Models\HostingAccount;
use App\Models\Server;
use App\Services\CPanel\CPanelApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - PitoHub Web Management & Interactive API Portal
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Web Quick Actions for UI
Route::post('/web/servers', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'hostname' => 'required|string|max:255',
        'ip_address' => 'nullable|string',
        'port' => 'nullable|integer',
        'username' => 'required|string',
        'api_token' => 'nullable|string',
        'mock_mode' => 'nullable|boolean',
    ]);

    $validated['mock_mode'] = $request->has('mock_mode');
    Server::create($validated);

    return redirect()->route('dashboard')->with('success', "Server '{$validated['name']}' added successfully!");
})->name('web.servers.store');

Route::post('/web/accounts', function (Request $request) {
    $validated = $request->validate([
        'server_id' => 'required|exists:servers,id',
        'domain' => 'required|string',
        'username' => 'required|string|alpha_num|max:16|unique:hosting_accounts,username',
        'plan' => 'required|string',
        'contact_email' => 'nullable|email',
        'disk_limit_mb' => 'nullable|integer',
    ]);

    $server = Server::findOrFail($validated['server_id']);
    $service = CPanelApiService::forServer($server);
    $service->createAccount($validated);

    $acc = HostingAccount::create([
        'server_id' => $server->id,
        'domain' => strtolower($validated['domain']),
        'username' => strtolower($validated['username']),
        'plan' => $validated['plan'],
        'contact_email' => $validated['contact_email'] ?? null,
        'disk_limit_mb' => $validated['disk_limit_mb'] ?? 5120,
        'bandwidth_limit_mb' => 51200,
        'ip_address' => $server->ip_address,
        'cpanel_created_at' => now(),
    ]);

    $acc->domains()->create([
        'domain_name' => $acc->domain,
        'type' => 'main',
        'document_root' => 'public_html',
    ]);

    return redirect()->route('dashboard')->with('success', "cPanel account '{$acc->username}' created successfully!");
})->name('web.accounts.store');

Route::post('/web/accounts/{account}/toggle-suspension', function (HostingAccount $account) {
    $service = CPanelApiService::forServer($account->server);

    if ($account->is_suspended) {
        $service->unsuspendAccount($account->username);
        $account->update(['is_suspended' => false, 'suspension_reason' => null, 'suspended_at' => null]);
        $msg = "Account '{$account->username}' unsuspended.";
    } else {
        $service->suspendAccount($account->username, 'Manual suspension via PitoHub UI');
        $account->update(['is_suspended' => true, 'suspension_reason' => 'Manual suspension via UI', 'suspended_at' => now()]);
        $msg = "Account '{$account->username}' suspended.";
    }

    return redirect()->route('dashboard')->with('success', $msg);
})->name('web.accounts.toggle-suspension');

Route::post('/web/servers/{server}/sync', function (Server $server) {
    $controller = app(\App\Http\Controllers\Api\ServerController::class);
    $controller->syncAccounts($server);

    return redirect()->route('dashboard')->with('success', "Accounts synced from server '{$server->name}'!");
})->name('web.servers.sync');
