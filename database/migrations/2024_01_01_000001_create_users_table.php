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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique();
            $table->string('password');
            $table->enum('gender', ['male', 'female', 'other'])->default('other');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_visible')->default(true); // Ghost mode control
            $table->boolean('is_banned')->default(false);
            $table->string('profile_image')->nullable();
            $table->text('bio')->nullable();
            $table->string('language')->default('fr'); // fr or ar
            $table->string('fcm_token')->nullable(); // For push notifications
            $table->string('google_id')->nullable();
            $table->string('apple_id')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            // Indexes for performance
            $table->index(['latitude', 'longitude']);
            $table->index('is_visible');
            $table->index('is_banned');
            $table->index('last_active_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
