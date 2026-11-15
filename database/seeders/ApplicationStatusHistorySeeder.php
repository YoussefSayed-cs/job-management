<?php

namespace Database\Seeders;

use App\Concerns\SeedsFromJson;
use Illuminate\Database\Seeder;
use Shagalni\Shared\Models\ApplicationStatusHistory;

class ApplicationStatusHistorySeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        $this->seedFromJson(ApplicationStatusHistory::class, 'application_status_histories');
    }
}
