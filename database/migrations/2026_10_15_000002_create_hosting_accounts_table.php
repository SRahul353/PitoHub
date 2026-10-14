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
        Schema::create('hosting_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained('servers')->cascadeOnDelete();
            $table->string('domain')->index();
            $table->string('username')->unique();
            $table->string('contact_email')->nullable();
            $table->string('plan')->default('Standard');
            $table->unsignedBigInteger('disk_limit_mb')->default(5120); // 0 or high number = unlimited
            $table->decimal('disk_used_mb', 10, 2)->default(0);
            $table->unsignedBigInteger('bandwidth_limit_mb')->default(51200);
            $table->decimal('bandwidth_used_mb', 10, 2)->default(0);
            $table->string('ip_address')->nullable();
            $table->boolean('is_suspended')->default(false);
            $table->string('suspension_reason')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('cpanel_created_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosting_accounts');
    }
};
