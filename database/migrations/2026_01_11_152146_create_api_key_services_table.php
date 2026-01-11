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
        Schema::create('api_key_services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('api_key_id');
            $table->enum('service_type', ['call_center', 'hr']);
            $table->timestamps();

            $table->foreign('api_key_id')->references('id')->on('api_keys')->onDelete('cascade');
            $table->unique(['api_key_id', 'service_type']);
            $table->index('service_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_key_services');
    }
};
