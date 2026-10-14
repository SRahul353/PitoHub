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
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('hostname');
            $table->string('ip_address')->nullable();
            $table->unsignedInteger('port')->default(2087);
            $table->enum('protocol', ['https', 'http'])->default('https');
            $table->string('username')->default('root');
            $table->text('api_token')->nullable(); // WHM API Token (encrypted)
            $table->text('password')->nullable(); // Optional password (encrypted)
            $table->enum('auth_type', ['api_token', 'password'])->default('api_token');
            $table->enum('status', ['active', 'offline', 'maintenance'])->default('active');
            $table->boolean('mock_mode')->default(false); // Enable simulation mode for testing/demo
            $table->json('nameservers')->nullable();
            $table->unsignedInteger('max_accounts')->default(100);
            $table->timestamp('last_synced_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
