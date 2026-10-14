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
        Schema::create('hosting_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hosting_account_id')->constrained('hosting_accounts')->cascadeOnDelete();
            $table->string('domain_name');
            $table->enum('type', ['main', 'addon', 'subdomain', 'alias'])->default('addon');
            $table->string('document_root')->default('public_html');
            $table->boolean('has_ssl')->default(false);
            $table->timestamps();

            $table->unique(['hosting_account_id', 'domain_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosting_domains');
    }
};
