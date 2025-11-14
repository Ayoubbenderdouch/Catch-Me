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
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json, text
            $table->string('group')->default('general'); // general, features, limits, content
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('app_settings')->insert([
            [
                'key' => 'max_distance',
                'value' => '50',
                'type' => 'integer',
                'group' => 'limits',
                'description' => 'Maximum distance in meters for nearby users',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'ghost_mode_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Enable or disable ghost mode globally',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'terms_content',
                'value' => 'Terms and conditions content here...',
                'type' => 'text',
                'group' => 'content',
                'description' => 'Terms and conditions',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'privacy_content',
                'value' => 'Privacy policy content here...',
                'type' => 'text',
                'group' => 'content',
                'description' => 'Privacy policy',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
