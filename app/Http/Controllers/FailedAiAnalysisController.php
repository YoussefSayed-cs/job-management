<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Shagalni\Shared\Enums\AiAnalysisStatus;
use Shagalni\Shared\Models\AiAnalysis;

class FailedAiAnalysisController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewFailed', AiAnalysis::class);

        $analyses = AiAnalysis::query()
            ->select(['id', 'application_id', 'status', 'attempts', 'failure_reason', 'updated_at'])
            ->where('status', AiAnalysisStatus::Failed)
            ->with('application:id,job_title,company_name,submitted_at')
            ->latest('updated_at')->orderBy('id')->paginate(15);

        return view('ai-analyses.failed', compact('analyses'));
    }
}