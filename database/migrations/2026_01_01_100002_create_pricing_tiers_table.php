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
        Schema::create('pricing_tiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('service_type', ['call_center', 'hr']);
            $table->integer('min_tokens')->comment('Minimum cumulative usage for this tier');
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('price_per_1k_tokens', 10, 4)->nullable()->comment('Override price at this tier');
            $table->timestamps();

            $table->index(['service_type', 'min_tokens']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_tiers');
    }
};
