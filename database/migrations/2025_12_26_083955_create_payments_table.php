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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->uuid('service_account_id')->nullable();
            $table->string('transaction_id')->unique()->nullable();
            $table->string('qicard_payment_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('IQD');
            $table->enum('type', ['topup', 'refund', 'adjustment', 'usage'])->default('topup');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->text('description')->nullable();
            $table->string('payment_method')->nullable();
            $table->json('metadata')->nullable();
            $table->json('qicard_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('service_account_id');
            $table->index('transaction_id');
            $table->index('qicard_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
