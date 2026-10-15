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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user')->after('password');
            }
        });

        Schema::table('servers', function (Blueprint $table) {
            if (! Schema::hasColumn('servers', 'server_type')) {
                $table->string('server_type')->default('cpanel_shared')->after('hostname');
            }
        });

        Schema::table('hosting_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('hosting_accounts', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('server_id')->constrained()->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hosting_accounts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('server_type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
