<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('two_factor_trusted_devices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('portal', 20);
            $table->string('site_legacy_key', 100)->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('selector', 32)->unique();
            $table->string('token_hash', 64);
            $table->string('user_agent_hash', 64);
            $table->string('password_signature', 64);
            $table->dateTime('expires_at');
            $table->dateTime('last_used_at')->nullable();
            $table->dateTime('created_at');

            $table->index(['portal', 'user_id']);
            $table->index(['portal', 'site_legacy_key', 'user_id'], 'trusted_2fa_portal_site_user_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('two_factor_trusted_devices');
    }
};
