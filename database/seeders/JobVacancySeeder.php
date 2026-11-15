<?php

namespace Database\Seeders;

use App\Concerns\SeedsFromJson;
use Illuminate\Database\Seeder;
use Shagalni\Shared\Models\JobVacancy;

class JobVacancySeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        $this->seedFromJson(JobVacancy::class, 'job_vacancies');
    }
}
