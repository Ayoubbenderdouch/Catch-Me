<?php

namespace Database\Seeders;

use App\Models\Message;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample messages between matched users
        $messages = [
            // Conversation between User 1 and User 3 (matched)
            [
                'sender_id' => 1,
                'receiver_id' => 3,
                'message' => 'Bonjour Sophie! Comment vas-tu?',
                'is_read' => true,
                'read_at' => now(),
                'created_at' => now()->subHours(2),
            ],
            [
                'sender_id' => 3,
                'receiver_id' => 1,
                'message' => 'Salut Marie! Je vais bien, merci. Et toi?',
                'is_read' => true,
                'read_at' => now()->subHours(1),
                'created_at' => now()->subHours(1),
            ],
            [
                'sender_id' => 1,
                'receiver_id' => 3,
                'message' => 'Très bien aussi! Tu veux prendre un café?',
                'is_read' => false,
                'created_at' => now()->subMinutes(30),
            ],

            // Conversation between User 2 and User 4 (matched)
            [
                'sender_id' => 2,
                'receiver_id' => 4,
                'message' => 'مرحبا عمر! كيف حالك؟',
                'is_read' => true,
                'read_at' => now()->subMinutes(45),
                'created_at' => now()->subHours(1),
            ],
            [
                'sender_id' => 4,
                'receiver_id' => 2,
                'message' => 'أهلا أحمد! بخير والحمد لله. ماذا عنك؟',
                'is_read' => true,
                'read_at' => now()->subMinutes(30),
                'created_at' => now()->subMinutes(45),
            ],
        ];

        foreach ($messages as $message) {
            Message::create($message);
        }
    }
}
