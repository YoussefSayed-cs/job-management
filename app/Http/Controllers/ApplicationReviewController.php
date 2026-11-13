<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Http\Requests\Application\UpdateApplicationStatusRequest;
use App\Models\Application;
use App\Services\ApplicationStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ApplicationReviewController extends Controller
{
    public function __construct(
        private readonly ApplicationStatusService $statusService
    ) {}

    /**
     * List applications belonging to the authenticated owner's company.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Application::class);

        $company = $request->user()->company()->firstOrFail();

        $statusFilter = $request->query('status');

        $search = $request->query('search');

        $applications = Application::query()
            ->where('company_id', $company->id)
            ->with([
                'user:id,full_name,email,phone,profile_picture',
                'jobVacancy:id,title,status',
            ])
            ->when($statusFilter, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('job_title', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('full_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })->latest('submitted_at')->paginate(15)->withQueryString();
            
            
            

        $statuses = ApplicationStatus::cases();

        return view('applications.index', compact( 'applications','statuses','statusFilter','search'));
           
    }

    /**
     * Show applicant details and application history.
     */
    public function show(Application $application): View
    {
        Gate::authorize('view', $application);

        $application->load([
            'user:id,full_name,email,phone,profile_picture',
            'jobVacancy:id,title,status',
            'resumeSnapshot',
            'statusHistories' => fn ($query) => $query->latest(),
            'statusHistories.changedBy:id,full_name',
            'aiAnalysis',
        ]);

        return view('applications.show', [
            'application' => $application,
            'availableStatuses' => $this->availableStatuses(
                $application->status
            ),
        ]);
    }

    /**
     * Update an application's status.
     */
    public function updateStatus(
        UpdateApplicationStatusRequest $request,
        Application $application
    ): RedirectResponse {
        $validated = $request->validated();

        $this->statusService->updateStatus(
            $application,
            ApplicationStatus::from($validated['status']),
            $request->user(),
            $validated['message'] ?? null
        );

        return redirect()->route('applications.show', $application)->with('status', 'Application status updated successfully.');
           
           
    }

    /**
     * Download the private Resume Snapshot.
     */
    public function downloadResume(Application $application)
    {
        Gate::authorize('view', $application);

        $snapshot = $application->resumeSnapshot;

        abort_unless($snapshot, 404);

        abort_unless( Storage::disk('local')->exists($snapshot->file_path),404 );
           
        $path = Storage::disk('local')->path($snapshot->file_path);
        return response()->download($path, $snapshot->original_name);  

    }

    /**
     * Return only valid next statuses for the current status.
     */
    private function availableStatuses(ApplicationStatus $current ):
        
    array {
        if ($current->isTerminal()) {
            return [];
        }

        $nextStatuses = [
            ApplicationStatus::Pending->value =>
                ApplicationStatus::UnderReview,

            ApplicationStatus::UnderReview->value =>
                ApplicationStatus::Shortlisted,

            ApplicationStatus::Shortlisted->value =>
                ApplicationStatus::Interview,

            ApplicationStatus::Interview->value =>
                ApplicationStatus::Accepted,
        ];

        $next = $nextStatuses[$current->value] ?? null;

        return array_values(array_filter([$next,ApplicationStatus::Rejected,]));
           
            
        
    }
}
