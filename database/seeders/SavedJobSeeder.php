<?php

namespace Database\Seeders;

use App\Models\SavedJob;
use App\Concerns\SeedsFromJson;
use Illuminate\Database\Seeder;

class SavedJobSeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        $this->seedFromJson(SavedJob::class, 'saved_jobs');
    }
}
