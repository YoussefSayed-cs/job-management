<?php

use App\Services\AnalyticsService;
use Shagalni\Shared\Enums\ApplicationStatus;
use Shagalni\Shared\Enums\JobStatus;
use Shagalni\Shared\Enums\UserRole;
use Shagalni\Shared\Models\Application;
use Shagalni\Shared\Models\JobCategory;
use Shagalni\Shared\Models\User;

it('allows an admin to view any company analytics', function () {
    $owner = approvedOwner();
    makeApplicationFor($owner, null, ApplicationStatus::Accepted);

    $this->actingAs(adminUser())
        ->get(route('companies.analytics', $owner->company))
        ->assertOk()
        ->assertSee($owner->company->name)
        ->assertViewHas('overview', fn (array $overview) => $overview['applications'] === 1);
});

it('allows a company owner to view only their own analytics', function () {
    $owner = approvedOwner();
    $owner->company->update(['name' => 'My Company']);

    $otherOwner = approvedOwner();
    $otherOwner->company->update(['name' => 'Other Company']);

    makeApplicationFor($owner, null, ApplicationStatus::Rejected);
    makeApplicationFor($otherOwner, null, ApplicationStatus::Accepted);

    $this->actingAs($owner)
        ->get(route('my-company.analytics'))
        ->assertOk()
        ->assertSee('My Company')
        ->assertDontSee('Other Company')
        ->assertViewHas('overview', fn (array $overview) => $overview['applications'] === 1
            && $overview['rejection_rate'] === 100.0);
});

it('prevents a company owner from viewing another company analytics', function () {
    $owner = approvedOwner();
    $otherOwner = approvedOwner();

    $this->actingAs($owner)
        ->get(route('companies.analytics', $otherOwner->company))
        ->assertForbidden();
});

it('prevents job seekers from accessing analytics', function () {
    $owner = approvedOwner();

    $this->actingAs(User::factory()->create(['role' => UserRole::JobSeeker]))
        ->get(route('my-company.analytics'))
        ->assertForbidden();

    $this->actingAs(User::factory()->create(['role' => UserRole::JobSeeker]))
        ->get(route('companies.analytics', $owner->company))
        ->assertForbidden();
});

it('includes deleted-job applications in company totals but excludes them from the per-job breakdown', function () {
    $owner = approvedOwner();
    $job = $owner->company->jobVacancies()->create([
        'job_category_id' => JobCategory::firstOrCreate(['name' => 'Analytics'])->id,
        'title' => 'Existing job',
        'description' => 'Role description.',
        'requirements' => 'Requirements.',
        'location' => 'Cairo',
        'employment_type' => 'full_time',
        'work_location_type' => 'remote',
        'status' => JobStatus::Active,
    ]);

    $application = Application::create([
        'job_vacancy_id' => $job->id,
        'company_id' => $owner->company->id,
        'user_id' => User::factory()->create()->id,
        'status' => ApplicationStatus::Pending,
        'job_title' => $job->title,
        'company_name' => $owner->company->name,
        'submitted_at' => now(),
    ]);

    $job->delete();
    $application->refresh();

    expect($application->job_vacancy_id)->toBeNull();

    $analytics = app(AnalyticsService::class);
    $overview = $analytics->companyOverview($owner->company);

    expect($overview['jobs'])->toBe(0)
        ->and($overview['applications'])->toBe(1)
        ->and($analytics->applicationsPerJob($owner->company))->toBe([]);
});

it('uses the accepted plus rejected denominator for company rates and handles zero decisions safely', function () {
    $owner = approvedOwner();
    makeApplicationFor($owner, null, ApplicationStatus::Accepted);
    makeApplicationFor($owner, null, ApplicationStatus::Rejected);
    makeApplicationFor($owner, null, ApplicationStatus::Pending);

    $analytics = app(AnalyticsService::class);
    $overview = $analytics->companyOverview($owner->company);

    expect($overview['acceptance_rate'])->toBe(50.0)
        ->and($overview['rejection_rate'])->toBe(50.0);

    $emptyOwner = approvedOwner();
    $emptyOverview = $analytics->companyOverview($emptyOwner->company);

    expect($emptyOverview['acceptance_rate'])->toBeNull()
        ->and($emptyOverview['rejection_rate'])->toBeNull();
});

it('returns a paginated, company-scoped breakdown of applications per job', function () {
    $owner = approvedOwner();
    $jobA = $owner->company->jobVacancies()->create([
        'job_category_id' => JobCategory::firstOrCreate(['name' => 'Analytics'])->id,
        'title' => 'Job A',
        'description' => 'Role A.',
        'requirements' => 'Requirements.',
        'location' => 'Cairo',
        'employment_type' => 'full_time',
        'work_location_type' => 'remote',
        'status' => JobStatus::Active,
    ]);
    $jobB = $owner->company->jobVacancies()->create([
        'job_category_id' => JobCategory::firstOrCreate(['name' => 'Analytics'])->id,
        'title' => 'Job B',
        'description' => 'Role B.',
        'requirements' => 'Requirements.',
        'location' => 'Cairo',
        'employment_type' => 'full_time',
        'work_location_type' => 'remote',
        'status' => JobStatus::Active,
    ]);

    Application::create(['job_vacancy_id' => $jobA->id, 'company_id' => $owner->company->id, 'user_id' => User::factory()->create()->id, 'status' => ApplicationStatus::Pending, 'job_title' => $jobA->title, 'company_name' => $owner->company->name, 'submitted_at' => now()]);
    Application::create(['job_vacancy_id' => $jobA->id, 'company_id' => $owner->company->id, 'user_id' => User::factory()->create()->id, 'status' => ApplicationStatus::Pending, 'job_title' => $jobA->title, 'company_name' => $owner->company->name, 'submitted_at' => now()]);
    Application::create(['job_vacancy_id' => $jobB->id, 'company_id' => $owner->company->id, 'user_id' => User::factory()->create()->id, 'status' => ApplicationStatus::Pending, 'job_title' => $jobB->title, 'company_name' => $owner->company->name, 'submitted_at' => now()]);

    $this->actingAs($owner)
        ->get(route('my-company.analytics'))
        ->assertOk()
        ->assertViewHas('jobBreakdown', fn ($breakdown) => $breakdown->total() === 2
            && $breakdown->items()[0]->title === 'Job A'
            && $breakdown->items()[0]->applications_count === 2
            && $breakdown->items()[1]->title === 'Job B'
            && $breakdown->items()[1]->applications_count === 1);
});
