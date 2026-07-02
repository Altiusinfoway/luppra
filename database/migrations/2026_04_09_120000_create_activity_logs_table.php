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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('module', 80);
            $table->string('action', 80);
            $table->string('event_key', 120)->nullable();
            $table->string('subject_type', 191);
            $table->unsignedBigInteger('subject_id');
            $table->string('reference_type', 191)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['subject_type', 'subject_id', 'created_at'], 'activity_logs_subject_idx');
            $table->index(['reference_type', 'reference_id', 'created_at'], 'activity_logs_reference_idx');
            $table->index(['module', 'action', 'created_at'], 'activity_logs_module_action_idx');
            $table->index(['event_key', 'created_at'], 'activity_logs_event_key_idx');
            $table->index(['user_id', 'created_at'], 'activity_logs_user_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
