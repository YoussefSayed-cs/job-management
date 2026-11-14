<?php

namespace Database\Seeders;

use App\Concerns\SeedsFromJson;
use App\Models\JobCategory;
use Illuminate\Database\Seeder;

class JobCategorySeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        $this->seedFromJson(JobCategory::class, 'job_categories');
    }
}
