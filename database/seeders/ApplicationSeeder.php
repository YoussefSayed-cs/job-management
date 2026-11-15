<?php

namespace Database\Seeders;

use App\Concerns\SeedsFromJson;
use Illuminate\Database\Seeder;
use Shagalni\Shared\Models\Application;
use Shagalni\Shared\Models\JobVacancy;

class ApplicationSeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {

        $companyIds = JobVacancy::pluck('company_id', 'id');

        $this->seedFromJson(Application::class, 'applications', function (array $row) use ($companyIds) {
            $row['company_id'] = $companyIds[$row['job_vacancy_id']] ?? null;

            return $row;
        });
    }
}
