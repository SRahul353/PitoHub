<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DatabaseEntity;
use App\Models\HostingAccount;
use App\Services\CPanel\CPanelApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DatabaseEntityController extends Controller
{
    /**
     * List databases
     */
    public function index(HostingAccount $account): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $account->databases()->latest()->get(),
        ]);
    }

    /**
     * Create MySQL Database via cPanel UAPI Mysql::create_database
     */
    public function store(Request $request, HostingAccount $account): JsonResponse
    {
        $validated = $request->validate([
            'database_name' => 'required|string|alpha_dash|max:64',
            'create_user' => 'nullable|boolean',
            'db_username' => 'nullable|string|alpha_dash|max:16',
            'db_password' => 'nullable|string|min:8',
        ]);

        // In cPanel, DB names are prefixed with cpanel user, e.g. "user_dbname"
        $dbName = str_starts_with($validated['database_name'], $account->username . '_')
            ? $validated['database_name']
            : $account->username . '_' . $validated['database_name'];

        $service = CPanelApiService::forServer($account->server);
        $res = $service->uapiCall($account->username, 'Mysql', 'create_database', [
            'name' => $dbName,
        ]);

        $dbUser = null;
        if (!empty($validated['create_user']) && !empty($validated['db_username']) && !empty($validated['db_password'])) {
            $dbUser = str_starts_with($validated['db_username'], $account->username . '_')
                ? $validated['db_username']
                : $account->username . '_' . $validated['db_username'];

            // Create DB user
            $service->uapiCall($account->username, 'Mysql', 'create_user', [
                'name' => $dbUser,
                'password' => $validated['db_password'],
            ]);

            // Set all privileges
            $service->uapiCall($account->username, 'Mysql', 'set_privileges_on_database', [
                'user' => $dbUser,
                'database' => $dbName,
                'privileges' => 'ALL PRIVILEGES',
            ]);
        }

        $db = $account->databases()->create([
            'database_name' => $dbName,
            'db_user' => $dbUser,
            'size_mb' => 0,
        ]);

        ActivityLog::log(
            'database.create',
            "Created database {$dbName} for account {$account->username}",
            'DatabaseEntity',
            (string) $db->id
        );

        return response()->json([
            'success' => true,
            'message' => "Database {$dbName} created successfully.",
            'data' => $db,
            'uapi_response' => $res,
        ], 201);
    }

    /**
     * Delete MySQL database via cPanel UAPI Mysql::delete_database
     */
    public function destroy(HostingAccount $account, DatabaseEntity $database): JsonResponse
    {
        if ($database->hosting_account_id !== $account->id) {
            return response()->json(['error' => 'Resource not found for this account'], 404);
        }

        $service = CPanelApiService::forServer($account->server);
        $service->uapiCall($account->username, 'Mysql', 'delete_database', [
            'name' => $database->database_name,
        ]);

        $dbName = $database->database_name;
        $database->delete();

        ActivityLog::log(
            'database.delete',
            "Deleted database {$dbName} for user {$account->username}",
            'HostingAccount',
            (string) $account->id
        );

        return response()->json([
            'success' => true,
            'message' => "Database {$dbName} deleted successfully.",
        ]);
    }
}
