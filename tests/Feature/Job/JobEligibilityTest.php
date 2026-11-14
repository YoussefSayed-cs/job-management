<?php

use App\Enums\AccountStatus;
use App\Enums\JobStatus;
use App\Enums\RegistrationStatus;
use App\Models\JobCategory;
use App\Models\JobVacancy;
use App\Models\User;

function eligibilityJob(User $owner, array $attributes = []): JobVacancy
{
    $category = JobCategory::firstOrCreate(['name' => 'Software']);

    return $owner->company->jobVacancies()->create(array_merge([
        'job_category_id' => $category->id,
        'title' => 'Laravel Developer',
        'description' => 'Build Laravel applications.',
        'requirements' => 'PHP and Laravel experience.',
        'location' => 'Cairo',
        'employment_type' => 'full_time',
        'work_location_type' => 'hybrid',
        'status' => JobStatus::Active,
    ], $attributes));
}

/**
 * The model method and the query scope must always agree.
 */
function expectEligibility(JobVacancy $job, bool $expected): void
{
    $job = $job->fresh();

    expect($job->isAcceptingApplications())->toBe($expected);

    expect(
        JobVacancy::query()->publiclyVisible()->whereKey($job->id)->exists()
    )->toBe($expected);
}

it('accepts an active job of an approved company with an active owner', function () {
    $job = eligibilityJob(approvedOwner());

    expectEligibility($job, true);
});

it('rejects a closed job', function () {
    $job = eligibilityJob(approvedOwner(), ['status' => JobStatus::Closed]);

    expectEligibility($job, false);
});

it('rejects an archived job', function () {
    $job = eligibilityJob(approvedOwner(), [
        'status' => JobStatus::Archived,
        'archived_at' => now(),
    ]);

    expectEligibility($job, false);
});

it('rejects a job whose deadline has passed', function () {
    $job = eligibilityJob(approvedOwner(), [
        'application_deadline' => now()->subDay()->toDateString(),
    ]);

    expectEligibility($job, false);
});

it('still accepts applications on the deadline day', function () {
    $job = eligibilityJob(approvedOwner(), [
        'application_deadline' => now()->toDateString(),
    ]);

    expectEligibility($job, true);
});

it('accepts a job without a deadline', function () {
    $job = eligibilityJob(approvedOwner(), ['application_deadline' => null]);

    expectEligibility($job, true);
});

it('rejects jobs of a company that is not approved', function (RegistrationStatus $status) {
    $owner = approvedOwner();
    $job = eligibilityJob($owner);

    $owner->company->update(['registration_status' => $status]);

    expectEligibility($job, false);
})->with([
    RegistrationStatus::PendingApproval,
    RegistrationStatus::Rejected,
]);

it('hides jobs while the owner is suspended or deactivated and restores them on reactivation', function (AccountStatus $status) {
    $owner = approvedOwner();
    $job = eligibilityJob($owner);

    $owner->forceFill(['status' => $status])->save();
    expectEligibility($job, false);

    $owner->forceFill(['status' => AccountStatus::Active])->save();
    expectEligibility($job, true);
})->with([
    AccountStatus::Suspended,
    AccountStatus::Deactivated,
]);

it('does not bring back closed jobs when the owner is reactivated', function () {
    $owner = approvedOwner();
    $job = eligibilityJob($owner, ['status' => JobStatus::Closed]);

    $owner->forceFill(['status' => AccountStatus::Suspended])->save();
    $owner->forceFill(['status' => AccountStatus::Active])->save();

    expectEligibility($job, false);
});
