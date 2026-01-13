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
        Schema::create('pricing', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('service_type', ['call_center', 'hr']);
            $table->decimal('price_per_1k_tokens', 10, 4);
            $table->integer('min_tokens')->default(1)->comment('Minimum billable tokens');
            $table->string('currency', 3)->default('IQD');
            $table->timestamp('effective_from');
            $table->timestamp('effective_until')->nullable();
            $table->timestamps();

            $table->index(['service_type', 'effective_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing');
    }
};
