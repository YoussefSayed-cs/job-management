<?php

namespace App\Console\Commands;

use App\Services\JobLifecycleService;
use Illuminate\Console\Command;

class PurgeArchivedJobs extends Command
{
    protected $signature = 'jobs:purge-archived';

    protected $description = 'Permanently delete jobs archived for more than 30 days';

    public function handle(JobLifecycleService $lifecycle): int
    {
        $count = $lifecycle->purgeExpiredArchivedJobs();

        $this->info("Successfully purged {$count} archived jobs.");

        return self::SUCCESS;
    }
}
