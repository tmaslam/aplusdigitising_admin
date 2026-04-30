<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_topups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('website', 30)->default('1dollar');
            $table->decimal('amount', 12, 2);
            $table->string('status', 50)->default('pending');
            $table->string('stripe_reference', 255)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['website', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_topups');
    }
};
