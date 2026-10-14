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
        Schema::create('hosting_email_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hosting_account_id')->constrained('hosting_accounts')->cascadeOnDelete();
            $table->string('email')->unique();
            $table->string('login');
            $table->string('domain');
            $table->unsignedBigInteger('quota_mb')->default(1024); // 0 = unlimited
            $table->decimal('disk_used_mb', 10, 2)->default(0);
            $table->boolean('suspended_login')->default(false);
            $table->boolean('suspended_incoming')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosting_email_accounts');
    }
};
