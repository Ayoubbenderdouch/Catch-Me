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
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->string('action'); // e.g., 'banned_user', 'deleted_message', 'reviewed_report'
            $table->string('entity_type')->nullable(); // e.g., 'User', 'Message', 'Report'
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('description');
            $table->json('metadata')->nullable(); // Additional data about the action
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('admin_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};
