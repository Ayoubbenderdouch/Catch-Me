<?php

namespace Database\Seeders;

use App\Models\Report;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample reports
        $reports = [
            [
                'reporter_id' => 3,
                'reported_user_id' => 5,
                'reason' => 'Comportement inapproprié et messages harcelants.',
                'status' => 'pending',
                'created_at' => now()->subDays(2),
            ],
            [
                'reporter_id' => 1,
                'reported_user_id' => 5,
                'reason' => 'Faux profil et photos trompeuses.',
                'status' => 'pending',
                'created_at' => now()->subDays(1),
            ],
            [
                'reporter_id' => 4,
                'reported_user_id' => 6,
                'reason' => 'محتوى غير لائق في الملف الشخصي.',
                'status' => 'reviewed',
                'reviewed_by' => 1,
                'reviewed_at' => now()->subHours(5),
                'admin_notes' => 'Reviewed and warned the user.',
                'created_at' => now()->subDays(3),
            ],
        ];

        foreach ($reports as $report) {
            Report::create($report);
        }
    }
}
