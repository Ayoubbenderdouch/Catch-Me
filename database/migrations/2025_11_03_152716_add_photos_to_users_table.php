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
        Schema::table('users', function (Blueprint $table) {
            // Add photos JSON column to store array of photo URLs (max 9)
            $table->json('photos')->nullable()->after('profile_image');

            // Migrate existing profile_image to photos array
        });

        // Data migration: Move profile_image to photos[0]
        \DB::statement("
            UPDATE users
            SET photos = JSON_ARRAY(profile_image)
            WHERE profile_image IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('photos');
        });
    }
};
