<?php

use Shagalni\Shared\Enums\JobStatus;
use Shagalni\Shared\Enums\RegistrationStatus;
use Shagalni\Shared\Enums\UserRole;
use Shagalni\Shared\Models\Company;
use Shagalni\Shared\Models\JobCategory;
use Shagalni\Shared\Models\JobVacancy;
use Shagalni\Shared\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

function createDashboardJob(Company $company, array $attributes = []): JobVacancy
{
    $attributes = array_merge([
        'job_category_id' => JobCategory::firstOrCreate(['name' => 'Dashboard Test'])->id,
        'title' => 'Dashboard Test Job',
        'description' => 'A job used to test the company dashboard.',
        'requirements' => 'Test requirements.',
        'location' => 'Cairo',
        'employment_type' => 'full_time',
        'work_location_type' => 'remote',
        'status' => JobStatus::Active,
        'application_deadline' => null,
    ], $attributes);
    $protectedFields = ['status', 'archived_at', 'created_at'];
    $protectedAttributes = array_intersect_key($attributes, array_flip($protectedFields));
    $job = $company->jobVacancies()->make(array_diff_key($attributes, array_flip($protectedFields)));

    $job->forceFill($protectedAttributes)->save();

    return $job;
}

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('admin can visit the dashboard', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->get(route('dashboard'))->assertOk();
});

test('company owner with an approved company can visit the dashboard', function () {
    $owner = User::factory()->create(['role' => UserRole::CompanyOwner]);

    createCompanyRecord([
        'owner_id' => $owner->id,
        'name' => 'Acme Software',
        'industry' => 'Software',
        'location' => 'Cairo',
        'registration_status' => RegistrationStatus::Approved,
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('jobStats', [
            'total' => 0,
            'active' => 0,
            'expired' => 0,
            'closed' => 0,
            'archived' => 0,
        ])
        ->assertViewHas('recentJobs', fn ($jobs) => $jobs->isEmpty())
        ->assertSee('No jobs yet');
});

test('company owner sees only their own company job counts and recent jobs', function () {
    $owner = approvedOwner();
    $otherOwner = approvedOwner();

    createDashboardJob($owner->company, ['title' => 'My company job']);
    createDashboardJob($otherOwner->company, ['title' => 'Another company private job']);
    createDashboardJob($otherOwner->company, ['title' => 'Another company second job']);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('jobStats', fn (array $stats) => $stats['total'] === 1
            && $stats['active'] === 1)
        ->assertViewHas('recentJobs', fn ($jobs) => $jobs->pluck('title')->all() === ['My company job'])
        ->assertSee('My company job')
        ->assertDontSee('Another company private job')
        ->assertDontSee('Another company second job');
});

test('dashboard categorizes active and expired jobs without overlap', function () {
    $owner = approvedOwner();
    $this->travelTo(now()->setDate(2026, 11, 16)->setTime(12, 0));
    $today = now()->toDateString();

    createDashboardJob($owner->company, ['title' => 'No deadline']);
    createDashboardJob($owner->company, ['title' => 'Deadline today', 'application_deadline' => $today]);
    createDashboardJob($owner->company, ['title' => 'Future deadline', 'application_deadline' => now()->addDay()->toDateString()]);
    createDashboardJob($owner->company, ['title' => 'Past deadline', 'application_deadline' => now()->subDay()->toDateString()]);
    createDashboardJob($owner->company, [
        'title' => 'Closed past deadline',
        'status' => JobStatus::Closed,
        'application_deadline' => now()->subDay()->toDateString(),
    ]);
    createDashboardJob($owner->company, [
        'title' => 'Archived past deadline',
        'status' => JobStatus::Archived,
        'application_deadline' => now()->subDay()->toDateString(),
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('jobStats', [
            'total' => 6,
            'active' => 3,
            'expired' => 1,
            'closed' => 1,
            'archived' => 1,
        ]);
});

test('dashboard counts only persisted jobs and shows the five newest jobs across statuses', function () {
    $owner = approvedOwner();
    $jobs = collect();

    foreach (range(1, 7) as $index) {
        $jobs->push(createDashboardJob($owner->company, [
            'title' => "Dashboard job {$index}",
            'status' => match ($index % 3) {
                0 => JobStatus::Archived,
                1 => JobStatus::Active,
                2 => JobStatus::Closed,
            },
            'created_at' => now()->startOfDay()->addMinutes($index),
        ]));
    }

    $jobs->first()->delete();

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('jobStats', fn (array $stats) => $stats['total'] === 6)
        ->assertViewHas('recentJobs', function ($recentJobs) {
            return $recentJobs->count() === 5
                && $recentJobs->pluck('title')->all() === [
                    'Dashboard job 7',
                    'Dashboard job 6',
                    'Dashboard job 5',
                    'Dashboard job 4',
                    'Dashboard job 3',
                ]
                && $recentJobs->pluck('status')->unique()->count() === 3;
        })
        ->assertDontSee('Dashboard job 1')
        ->assertSee('Dashboard job 7')
        ->assertSee('Dashboard job 6')
        ->assertSee('Dashboard job 5')
        ->assertSee('Dashboard job 4')
        ->assertSee('Dashboard job 3');
});

test('job seekers cannot visit the backoffice dashboard', function () {
    $jobSeeker = User::factory()->create(['role' => UserRole::JobSeeker]);

    $this->actingAs($jobSeeker)->get(route('dashboard'))->assertForbidden();
});
