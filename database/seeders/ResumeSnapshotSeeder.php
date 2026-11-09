<?php

namespace Database\Seeders;

use App\Models\ResumeSnapshot;
use App\Concerns\SeedsFromJson;
use Illuminate\Database\Seeder;

class ResumeSnapshotSeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        $this->seedFromJson(ResumeSnapshot::class, 'resume_snapshots');
    }
}
