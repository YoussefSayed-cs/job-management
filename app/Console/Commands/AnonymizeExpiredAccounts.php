<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\AccountAnonymizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class AnonymizeExpiredAccounts extends Command
{
    protected $signature = 'accounts:anonymize-expired
                            {--dry-run : Only list the due accounts without changing anything}';

    protected $description = 'Anonymize accounts whose 30-day deletion grace period has ended';

    public function handle(AccountAnonymizationService $service): int
    {
        $cutoff = now()->subDays(AccountAnonymizationService::GRACE_PERIOD_DAYS);

        $query = User::query()
            ->whereNotNull('deletion_requested_at')
            ->where('deletion_requested_at', '<=', $cutoff)
            ->whereNull('anonymized_at')
            ->where('role', '!=', UserRole::Admin->value);

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('No accounts are due for anonymization.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("{$total} account(s) are due for anonymization (dry run, nothing changed):");

            (clone $query)->get()->each(function (User $user) {
                $this->line(" - {$user->id} (deletion requested at {$user->deletion_requested_at})");
            });

            return self::SUCCESS;
        }

        $done = 0;
        $failed = 0;

        // one bad account must never stop the others from being processed
        (clone $query)->chunkById(100, function ($users) use ($service, &$done, &$failed) {
            foreach ($users as $user) {
                try {
                    $account = $user instanceof User
                        ? $user
                        : User::query()->findOrFail($user->id);

                    $service->anonymize($account);
                    $done++;
                } catch (Throwable $e) {
                    $failed++;

                    Log::error('Automatic anonymization failed', [
                        'user_id' => $user->id,
                        'error'   => $e->getMessage(),
                    ]);
                }
            }
        });

        Log::info('Automatic anonymization finished', [
            'anonymized' => $done,
            'failed'     => $failed,
        ]);

        $this->info("Anonymized: {$done}, failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
