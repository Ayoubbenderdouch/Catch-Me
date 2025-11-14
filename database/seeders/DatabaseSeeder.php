<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Only seed admin account - all other data will be real from users
        $this->call([
            AdminSeeder::class,
            // UserSeeder::class,      // REMOVED - Real users only
            // LikeSeeder::class,      // REMOVED - Real likes only
            // MessageSeeder::class,   // REMOVED - Real messages only
            // ReportSeeder::class,    // REMOVED - Real reports only
        ]);
    }
}
