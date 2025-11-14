<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users with locations in Paris area
        $users = [
            [
                'name' => 'Marie Dupont',
                'email' => 'marie@example.com',
                'phone' => '+33612345678',
                'password' => Hash::make('password'),
                'gender' => 'female',
                'latitude' => 48.8566,
                'longitude' => 2.3522,
                'bio' => 'Bonjour! J\'adore voyager et rencontrer de nouvelles personnes.',
                'language' => 'fr',
                'is_visible' => true,
            ],
            [
                'name' => 'Ahmed Hassan',
                'email' => 'ahmed@example.com',
                'phone' => '+33612345679',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'latitude' => 48.8570,
                'longitude' => 2.3528,
                'bio' => 'مرحبا! أحب الرياضة والموسيقى.',
                'language' => 'ar',
                'is_visible' => true,
            ],
            [
                'name' => 'Sophie Martin',
                'email' => 'sophie@example.com',
                'phone' => '+33612345680',
                'password' => Hash::make('password'),
                'gender' => 'female',
                'latitude' => 48.8580,
                'longitude' => 2.3540,
                'bio' => 'Artiste passionnée par la photographie.',
                'language' => 'fr',
                'is_visible' => true,
            ],
            [
                'name' => 'Omar Benali',
                'email' => 'omar@example.com',
                'phone' => '+33612345681',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'latitude' => 48.8575,
                'longitude' => 2.3535,
                'bio' => 'مطور برمجيات وعاشق للتكنولوجيا.',
                'language' => 'ar',
                'is_visible' => true,
            ],
            [
                'name' => 'Camille Dubois',
                'email' => 'camille@example.com',
                'phone' => '+33612345682',
                'password' => Hash::make('password'),
                'gender' => 'other',
                'latitude' => 48.8590,
                'longitude' => 2.3550,
                'bio' => 'Designer graphique freelance.',
                'language' => 'fr',
                'is_visible' => true,
            ],
            [
                'name' => 'Ghost User',
                'email' => 'ghost@example.com',
                'phone' => '+33612345683',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'latitude' => 48.8600,
                'longitude' => 2.3560,
                'bio' => 'Je préfère rester invisible.',
                'language' => 'fr',
                'is_visible' => false, // Ghost mode
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
