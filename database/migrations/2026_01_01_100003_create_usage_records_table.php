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
        Schema::create('usage_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_account_id');
            $table->enum('service_type', ['call_center', 'hr']);
            $table->integer('tokens_used');
            $table->decimal('cost', 10, 4)->comment('Cost at time of usage - locked for price changes');
            $table->string('action_type')->nullable();
            $table->string('resource_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->foreign('service_account_id')->references('id')->on('service_accounts')->onDelete('cascade');
            $table->index(['service_account_id', 'recorded_at']);
            $table->index('recorded_at');
            $table->index('service_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_records');
    }
};
