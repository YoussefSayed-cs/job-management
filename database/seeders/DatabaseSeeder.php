<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        $this->call([
            UserSeeder::class,
            JobCategorySeeder::class,
            CompanySeeder::class,
            CompanyChangeRequestSeeder::class,
            JobVacancySeeder::class,
            SavedJobSeeder::class,
            ResumeSeeder::class,
            ApplicationSeeder::class,
            ResumeSnapshotSeeder::class,
            AiAnalysisSeeder::class,
            ApplicationStatusHistorySeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
