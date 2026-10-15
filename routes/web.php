<?php

use App\Http\Controllers\Api\ServerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WebAuthController;
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

// Authentication Routes
Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// Protected Web Dashboard Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Admin Only Actions
    Route::middleware('role:admin')->group(function () {
        Route::post('/web/servers', function (Request $request) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'hostname' => 'required|string|max:255',
                'server_type' => 'nullable|string|in:cpanel_shared,whm',
                'ip_address' => 'nullable|string',
                'port' => 'nullable|integer',
                'username' => 'required|string',
                'api_token' => 'nullable|string',
                'mock_mode' => 'nullable|boolean',
            ]);

            $validated['mock_mode'] = $request->has('mock_mode');
            $validated['port'] = $validated['port'] ?? ($validated['server_type'] === 'cpanel_shared' ? 2083 : 2087);
            Server::create($validated);

            return redirect()->route('dashboard')->with('success', "Server '{$validated['name']}' added successfully!");
        })->name('web.servers.store');

        Route::post('/web/accounts', function (Request $request) {
            $validated = $request->validate([
                'server_id' => 'required|exists:servers,id',
                'user_id' => 'nullable|exists:users,id',
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
                'user_id' => $validated['user_id'] ?? null,
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

        Route::post('/web/servers/{server}/update', function (Request $request, Server $server) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'hostname' => 'required|string|max:255',
                'server_type' => 'required|string|in:cpanel_shared,whm',
                'ip_address' => 'nullable|string',
                'port' => 'nullable|integer',
                'username' => 'required|string',
                'api_token' => 'nullable|string',
                'mock_mode' => 'nullable|boolean',
            ]);

            $validated['mock_mode'] = $request->has('mock_mode');
            if (empty($validated['api_token'])) {
                unset($validated['api_token']);
            }
            $server->update($validated);

            return redirect()->route('dashboard')->with('success', "cPanel/WHM configuration for '{$server->name}' updated successfully!");
        })->name('web.servers.update');

        Route::post('/web/servers/{server}/delete', function (Server $server) {
            $name = $server->name;
            $server->delete();

            return redirect()->route('dashboard')->with('success', "Server '{$name}' removed.");
        })->name('web.servers.delete');

        Route::post('/web/servers/{server}/sync', function (Server $server) {
            $controller = app(ServerController::class);
            $controller->syncAccounts($server);

            return redirect()->route('dashboard')->with('success', "Accounts synced from server '{$server->name}'!");
        })->name('web.servers.sync');
    });
});
