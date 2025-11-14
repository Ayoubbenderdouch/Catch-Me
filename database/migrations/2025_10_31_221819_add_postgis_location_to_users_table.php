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
        // PostGIS extension will be enabled later
        // For now, we're using the latitude/longitude columns from the users table
        // Uncomment these when PostGIS is properly configured:

        // DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');
        // DB::statement('ALTER TABLE users ADD COLUMN location geography(Point, 4326)');
        // DB::statement('CREATE INDEX users_location_gist ON users USING GIST(location)');
        // DB::statement("
        //     UPDATE users
        //     SET location = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)::geography
        //     WHERE latitude IS NOT NULL AND longitude IS NOT NULL
        // ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS users_location_gist');
        DB::statement('ALTER TABLE users DROP COLUMN IF EXISTS location');
    }
};
