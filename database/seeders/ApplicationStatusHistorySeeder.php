<?php

namespace Database\Seeders;

use App\Models\ApplicationStatusHistory;
use App\Concerns\SeedsFromJson;
use Illuminate\Database\Seeder;

class ApplicationStatusHistorySeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        $this->seedFromJson(ApplicationStatusHistory::class, 'application_status_histories');
    }
}
