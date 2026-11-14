<?php

namespace Database\Seeders;

use App\Concerns\SeedsFromJson;
use App\Models\Resume;
use Illuminate\Database\Seeder;

class ResumeSeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        $this->seedFromJson(Resume::class, 'resumes');
    }
}
