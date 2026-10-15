<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('user')->after('password');
            });
        } catch (\Throwable $e) {
            // Already added
        }

        try {
            Schema::table('servers', function (Blueprint $table) {
                $table->string('server_type')->default('cpanel_shared')->after('hostname');
            });
        } catch (\Throwable $e) {
            // Already added
        }

        try {
            Schema::table('hosting_accounts', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('server_id')->constrained()->nullOnDelete();
            });
        } catch (\Throwable $e) {
            // Already added
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('hosting_accounts', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('servers', function (Blueprint $table) {
                $table->dropColumn('server_type');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        } catch (\Throwable $e) {
        }
    }
};
