<?php

namespace Database\Seeders;

use App\Models\Like;
use Illuminate\Database\Seeder;

class LikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some sample likes and matches
        $likes = [
            // User 1 likes User 2 (pending)
            ['from_user_id' => 1, 'to_user_id' => 2, 'status' => 'pending'],

            // User 2 likes User 1 (accepted - mutual match)
            ['from_user_id' => 2, 'to_user_id' => 1, 'status' => 'accepted'],

            // User 1 and User 3 match
            ['from_user_id' => 1, 'to_user_id' => 3, 'status' => 'accepted'],
            ['from_user_id' => 3, 'to_user_id' => 1, 'status' => 'accepted'],

            // User 4 likes User 2 (pending)
            ['from_user_id' => 4, 'to_user_id' => 2, 'status' => 'pending'],

            // User 2 likes User 4 (accepted - mutual match)
            ['from_user_id' => 2, 'to_user_id' => 4, 'status' => 'accepted'],

            // User 5 likes User 3 (rejected)
            ['from_user_id' => 5, 'to_user_id' => 3, 'status' => 'rejected'],
        ];

        foreach ($likes as $like) {
            Like::create($like);
        }
    }
}
