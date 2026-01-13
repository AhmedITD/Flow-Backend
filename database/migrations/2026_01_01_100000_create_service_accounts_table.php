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
        Schema::create('service_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'suspended', 'closed'])->default('active');
            $table->decimal('balance', 12, 2)->default(0)->comment('Prepaid credit balance');
            $table->string('currency', 3)->default('IQD');
            $table->decimal('credit_limit', 12, 2)->default(0)->comment('Postpaid allowance (0 = prepaid only)');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_accounts');
    }
};
