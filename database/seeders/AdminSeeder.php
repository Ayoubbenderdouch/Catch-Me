<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@catchme.app',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        Admin::create([
            'name' => 'Report Moderator',
            'email' => 'moderator@catchme.app',
            'password' => Hash::make('password'),
            'role' => 'report_moderator',
            'is_active' => true,
        ]);

        Admin::create([
            'name' => 'Chat Moderator',
            'email' => 'chat@catchme.app',
            'password' => Hash::make('password'),
            'role' => 'chat_moderator',
            'is_active' => true,
        ]);
    }
}
