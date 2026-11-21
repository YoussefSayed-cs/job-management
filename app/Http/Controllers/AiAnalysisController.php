<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Shagalni\Shared\Enums\AiAnalysisStatus;
use Shagalni\Shared\Jobs\ProcessResumeAnalysis;
use Shagalni\Shared\Models\AiAnalysis;

class AiAnalysisController extends Controller
{
    public function retry(AiAnalysis $analysis): RedirectResponse
    {
        Gate::authorize('retry', $analysis);

        DB::transaction(function () use ($analysis) {
            $locked = AiAnalysis::whereKey($analysis->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->status === AiAnalysisStatus::Failed, 409);

            $locked->update([
                'status' => AiAnalysisStatus::Pending,
                'failure_reason' => null,
            ]);
        });

        ProcessResumeAnalysis::dispatch($analysis->application_id);

        return back()->with('status', 'The analysis has been queued again.');
    }
}
