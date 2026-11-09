<?php

namespace Database\Seeders;

use App\Models\Resume;
use App\Concerns\SeedsFromJson;
use Illuminate\Database\Seeder;

class ResumeSeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        $this->seedFromJson(Resume::class, 'resumes');
    }
}
