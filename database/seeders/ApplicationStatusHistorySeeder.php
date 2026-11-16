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
        ApplicationStatusHistory::withoutEvents(function () {
            $this->seedFromJson(ApplicationStatusHistory::class, 'application_status_histories');
        });
    }
}
