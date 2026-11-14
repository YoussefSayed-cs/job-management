<?php

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\User;
use App\Services\AnalyticsService;

function analytics(): AnalyticsService
{
    return app(AnalyticsService::class);
}

it('calculates acceptance and rejection rates from accepted plus rejected only', function () {
    $owner = approvedOwner();

    foreach ([
        ApplicationStatus::Accepted, ApplicationStatus::Accepted, ApplicationStatus::Accepted,
        ApplicationStatus::Rejected,
        ApplicationStatus::Pending, ApplicationStatus::Pending,
        ApplicationStatus::Withdrawn,
    ] as $status) {
        makeApplicationFor($owner, null, $status);
    }

    $overview = analytics()->companyOverview($owner->company);

    expect($overview['applications'])->toBe(7)
        ->and($overview['acceptance_rate'])->toBe(75.0)
        ->and($overview['rejection_rate'])->toBe(25.0)
        ->and($overview['status_distribution']['pending'])->toBe(2);
});

it('returns null rates when there are no final decisions', function () {
    $owner = approvedOwner();
    makeApplicationFor($owner);

    $overview = analytics()->companyOverview($owner->company);

    expect($overview['acceptance_rate'])->toBeNull()->and($overview['rejection_rate'])->toBeNull(); 
        
});

it('returns every status in the distribution even when empty', function () {
    $distribution = analytics()->statusDistribution();

    expect($distribution)->toHaveCount(count(ApplicationStatus::cases()))->and(array_sum($distribution))->toBe(0);
        
});

it('keeps applications of a deleted job in company totals but not in per-job breakdown', function () {
    $owner = approvedOwner();
    $application = makeApplicationFor($owner);

    $application->jobVacancy->delete();

    $application->refresh();
    expect($application->job_vacancy_id)->toBeNull();

    $overview = analytics()->companyOverview($owner->company);

    expect($overview['applications'])->toBe(1)
        ->and($overview['jobs'])->toBe(0)
        ->and(analytics()->applicationsPerJob($owner->company))->toBe([]);
});

it('scopes company analytics to that company only', function () {
    $ownerA = approvedOwner();
    $ownerB = approvedOwner();

    makeApplicationFor($ownerA);
    makeApplicationFor($ownerB);
    makeApplicationFor($ownerB);

    expect(analytics()->companyOverview($ownerA->company)['applications'])->toBe(1)
        ->and(analytics()->companyOverview($ownerB->company)['applications'])->toBe(2)
        ->and(analytics()->companyOverview($ownerB->company)['applicants'])->toBe(2);
});

it('counts applications per job ordered by most applications', function () {
    $owner = approvedOwner();

    $first = makeApplicationFor($owner);
    makeApplicationFor($owner);

    Application::create([
        'job_vacancy_id' => $first->job_vacancy_id,
        'company_id' => $owner->company->id,
        'user_id' => User::factory()->create()->id,
        'status' => ApplicationStatus::Pending,
        'job_title' => $first->job_title,
        'company_name' => $owner->company->name,
        'submitted_at' => now(),
    ]);

    $perJob = analytics()->applicationsPerJob($owner->company);

    expect($perJob)->toHaveCount(2)
        ->and($perJob[0]['applications'])->toBe(2)
        ->and($perJob[1]['applications'])->toBe(1);
});

it('does not count anonymized users in platform totals', function () {
    adminUser();
    User::factory()->create();
    User::factory()->create(['anonymized_at' => now()]);

    expect(analytics()->platformTotals()['users'])->toBe(2);
});

it('reports rolling 30-day activity and ignores older records', function () {
    $owner = approvedOwner();

    $recent = makeApplicationFor($owner);
    $old = makeApplicationFor($owner);
    $old->update(['submitted_at' => now()->subDays(31)]);

    
    ApplicationStatusHistory::create([
        'application_id' => $recent->id,
        'from_status' => null,
        'to_status' => ApplicationStatus::Pending,
    ]);
    ApplicationStatusHistory::create([
        'application_id' => $recent->id,
        'from_status' => ApplicationStatus::Pending,
        'to_status' => ApplicationStatus::UnderReview,
        'changed_by' => $owner->id,
    ]);

    $activity = analytics()->platformActivity();

    expect($activity['applications_submitted'])->toBe(1)
        ->and($activity['status_changes'])->toBe(1)
        ->and($activity['jobs_published'])->toBe(2)
        ->and($activity['company_registrations'])->toBe(1);
});

it('builds a zero-filled 30 day applications series', function () {
    $owner = approvedOwner();

    makeApplicationFor($owner);
    makeApplicationFor($owner)->update(['submitted_at' => now()->subDays(35)]);

    $series = analytics()->dailyApplications();

    expect($series)->toHaveCount(30)
        ->and($series[now()->toDateString()])->toBe(1)
        ->and(array_sum($series))->toBe(1);
});

it('lists top companies by number of applications', function () {
    $small = approvedOwner();
    $big = approvedOwner();
    $big->company->update(['name' => 'Big Co']);

    makeApplicationFor($small);
    makeApplicationFor($big);
    makeApplicationFor($big);

    $top = analytics()->topCompaniesByApplications();

    expect($top[0])->toBe(['name' => 'Big Co', 'applications' => 2])
        ->and($top)->toHaveCount(2);
});