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
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('ticket_id')->primary();
            $table->uuid('tenant_id')->index();
            $table->enum('channel', ['voice', 'chat'])->default('chat');
            $table->enum('status', ['open', 'pending', 'resolved', 'closed'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('category', ['billing', 'technical', 'shipping', 'account', 'general', 'other'])->default('general');
            $table->string('subject');
            $table->text('summary');
            $table->string('created_by_type')->default('system'); // 'agent' or 'system'
            $table->string('created_by_id')->nullable(); // agent_id or 'system'
            $table->foreignId('assigned_to')->nullable()->index()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'priority']);
            $table->index(['status', 'priority']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
