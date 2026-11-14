<?php

namespace App\Http\Controllers;

use App\Enums\JobStatus;
use App\Enums\UserRole;
use App\Http\Requests\Job\DeleteArchivedJobRequest;
use App\Http\Requests\Job\IndexJobsRequest;
use App\Http\Requests\Job\StoreJobRequest;
use App\Http\Requests\Job\UpdateJobRequest;
use App\Models\JobCategory;
use App\Models\JobVacancy;
use App\Services\JobLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class JobController extends Controller
{
    public function __construct(
        private readonly JobLifecycleService $lifecycle
    ) {}

    public function index(IndexJobsRequest $request): View
    {
        $filters = $request->validated();
        $user = $request->user();

        $query = JobVacancy::query()->with(['company', 'category']);

        if ($user->role === UserRole::CompanyOwner) {
            $query->where('company_id', $user->company->id);
        }

        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('company', function ($companyQuery) use ($search) {
                        $companyQuery->where('name', 'like', "%{$search}%");
                    });
            });
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })->when($filters['job_category_id'] ?? null, function ($query, $categoryId) {
            $query->where('job_category_id', $categoryId);
        })->when($filters['employment_type'] ?? null, function ($query, $type) {
            $query->where('employment_type', $type);
        })->when($filters['work_location_type'] ?? null, function ($query, $type) {
            $query->where('work_location_type', $type);
        })->when($filters['experience_level'] ?? null, function ($query, $level) {
            $query->where('experience_level', $level);
        });

        $jobs = $query->latest()->paginate(10)->withQueryString();

        $categories = JobCategory::query()->orderBy('name')->get();

        return view('jobs.index', compact('jobs', 'categories', 'filters'));
    }

    public function create(): View
    {
        Gate::authorize('create', JobVacancy::class);

        $categories = JobCategory::query()->orderBy('name')->get();

        return view('jobs.create', compact('categories'));
    }

    public function store(StoreJobRequest $request): RedirectResponse
    {
        $company = $request->user()->company()->firstOrFail();

        $job = $company->jobVacancies()->create([...$request->validated(), 'status' => JobStatus::Active]);

        return redirect()->route('jobs.show', $job)->with('status', 'Job published successfully.');
    }

    public function show(JobVacancy $job): View
    {
        Gate::authorize('view', $job);

        $job->load(['company', 'category'])->loadCount('applications');

        return view('jobs.show', compact('job'));
    }

    public function edit(JobVacancy $job): View
    {
        Gate::authorize('update', $job);

        $categories = JobCategory::query()->orderBy('name')->get();

        return view('jobs.edit', compact('job', 'categories'));
    }

    public function update(UpdateJobRequest $request, JobVacancy $job): RedirectResponse
    {
        $job->update($request->validated());

        return redirect()->route('jobs.show', $job)->with('status', 'Job updated successfully.');
    }

    public function close(JobVacancy $job): RedirectResponse
    {
        Gate::authorize('close', $job);

        $this->lifecycle->close($job);

        return back()->with('status', 'Job closed successfully.');
    }

    public function reopen(JobVacancy $job): RedirectResponse
    {
        Gate::authorize('reopen', $job);

        $this->lifecycle->reopen($job);

        return back()->with('status', 'Job reopened successfully.');
    }

    public function archive(JobVacancy $job): RedirectResponse
    {
        Gate::authorize('archive', $job);

        $this->lifecycle->archive($job);

        return back()->with('status', 'Job archived successfully.');
    }

    public function restore(JobVacancy $job): RedirectResponse
    {
        Gate::authorize('restore', $job);

        $this->lifecycle->restore($job);

        return back()->with('status', 'Job restored successfully.');
    }

    public function destroy(DeleteArchivedJobRequest $request, JobVacancy $job): RedirectResponse
    {
        $this->lifecycle->permanentlyDelete($job);

        return redirect()->route('jobs.index')->with('status', 'Job permanently deleted.');
    }
}
