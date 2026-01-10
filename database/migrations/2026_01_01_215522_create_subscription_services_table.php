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
        Schema::create('subscription_services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('subscription_id');
            $table->string('service_type'); // Enum: call_center, hr
            $table->integer('allocated_tokens')->default(0); // Token limit for this subscription service
            $table->integer('tokens_used')->default(0); // Total tokens consumed
            $table->timestamp('reset_at');
            $table->timestamps();

            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
            $table->unique(['subscription_id', 'service_type']);
            $table->index('reset_at');
            $table->index('service_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_services');
    }
};
