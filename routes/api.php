<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CronJobEntityController;
use App\Http\Controllers\Api\DatabaseEntityController;
use App\Http\Controllers\Api\DomainEntityController;
use App\Http\Controllers\Api\EmailAccountController;
use App\Http\Controllers\Api\HostingAccountController;
use App\Http\Controllers\Api\ServerController;
use App\Http\Controllers\Api\SystemController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PitoHub REST API v1 Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Public Authentication Endpoints
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
    });

    // Protected API Endpoints (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {

        // Auth management
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // System statistics (user or admin)
        Route::get('/stats', [SystemController::class, 'overviewStats']);

        // Admin-only Server Management & Activity Logs
        Route::middleware('role:admin')->group(function () {
            Route::get('/activity-logs', [SystemController::class, 'activityLogs']);
            Route::apiResource('servers', ServerController::class);
            Route::post('/servers/{server}/test-connection', [ServerController::class, 'testConnection']);
            Route::post('/servers/{server}/sync-accounts', [ServerController::class, 'syncAccounts']);
            Route::get('/servers/{server}/stats', [ServerController::class, 'stats']);
        });

        // Hosting (cPanel) Accounts Management
        Route::apiResource('accounts', HostingAccountController::class);
        Route::post('/accounts/{account}/suspend', [HostingAccountController::class, 'suspend']);
        Route::post('/accounts/{account}/unsuspend', [HostingAccountController::class, 'unsuspend']);
        Route::post('/accounts/{account}/change-password', [HostingAccountController::class, 'changePassword']);
        Route::post('/accounts/{account}/sso-url', [HostingAccountController::class, 'ssoUrl']);

        // Account Sub-Resources
        // 1. Email Accounts
        Route::get('/accounts/{account}/emails', [EmailAccountController::class, 'index']);
        Route::post('/accounts/{account}/emails', [EmailAccountController::class, 'store']);
        Route::delete('/accounts/{account}/emails/{email}', [EmailAccountController::class, 'destroy']);

        // 2. MySQL Databases
        Route::get('/accounts/{account}/databases', [DatabaseEntityController::class, 'index']);
        Route::post('/accounts/{account}/databases', [DatabaseEntityController::class, 'store']);
        Route::delete('/accounts/{account}/databases/{database}', [DatabaseEntityController::class, 'destroy']);

        // 3. Domains & Subdomains
        Route::get('/accounts/{account}/domains', [DomainEntityController::class, 'index']);
        Route::post('/accounts/{account}/domains', [DomainEntityController::class, 'store']);
        Route::delete('/accounts/{account}/domains/{domain}', [DomainEntityController::class, 'destroy']);

        // 4. Cron Jobs
        Route::get('/accounts/{account}/cronjobs', [CronJobEntityController::class, 'index']);
        Route::post('/accounts/{account}/cronjobs', [CronJobEntityController::class, 'store']);
        Route::delete('/accounts/{account}/cronjobs/{cronjob}', [CronJobEntityController::class, 'destroy']);
    });
});
