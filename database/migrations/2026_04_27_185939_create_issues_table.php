<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('category', ['bug', 'feature', 'infrastructure', 'security', 'other'])->default('other');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');

            // AI / rules-generated fields
            $table->text('summary')->nullable();
            $table->text('next_action')->nullable();
            $table->string('summary_source')->nullable()->comment('claude_api | rules_based');

            // Escalation flag – set by business logic
            $table->boolean('escalated')->default(false);
            $table->timestamp('escalated_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Indexes for common filter patterns
            $table->index('status');
            $table->index('priority');
            $table->index('category');
            $table->index('escalated');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
