<?php

namespace Database\Seeders;

use App\Concerns\SeedsFromJson;
use App\Models\JobVacancy;
use Illuminate\Database\Seeder;

class JobVacancySeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        $this->seedFromJson(JobVacancy::class, 'job_vacancies');
    }
}
