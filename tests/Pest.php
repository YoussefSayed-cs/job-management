<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Shagalni\Shared\Enums\ApplicationStatus;
use Shagalni\Shared\Enums\RegistrationStatus;
use Shagalni\Shared\Enums\UserRole;
use Shagalni\Shared\Models\Application;
use Shagalni\Shared\Models\Company;
use Shagalni\Shared\Models\JobCategory;
use Shagalni\Shared\Models\User;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function adminUser(): User
{
    return User::factory()->create(['role' => UserRole::Admin]);
}

function createCompanyRecord(array $attributes): Company
{
    $protectedFields = [
        'owner_id',
        'registration_status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];
    $protectedAttributes = array_intersect_key($attributes, array_flip($protectedFields));
    $company = new Company(array_diff_key($attributes, array_flip($protectedFields)));

    $company->forceFill($protectedAttributes)->save();

    return $company;
}

function approvedOwner(): User
{
    $owner = User::factory()->create(['role' => UserRole::CompanyOwner]);

    createCompanyRecord([
        'owner_id' => $owner->id,
        'name' => 'Approved Co',
        'industry' => 'Software',
        'location' => 'Cairo',
        'registration_status' => RegistrationStatus::Approved,
    ]);

    return $owner;
}

function makeApplicationFor(
    User $owner,
    ?User $applicant = null,
    ApplicationStatus $status = ApplicationStatus::Pending
): Application {
    $applicant ??= User::factory()->create();

    $category = JobCategory::firstOrCreate(['name' => 'Software']);

    $job = $owner->company->jobVacancies()->create([
        'job_category_id' => $category->id,
        'title' => 'Backend Developer',
        'description' => 'Build backend services.',
        'requirements' => 'PHP experience.',
        'location' => 'Cairo',
        'employment_type' => 'full_time',
        'work_location_type' => 'on_site',
    ]);

    return Application::create([
        'job_vacancy_id' => $job->id,
        'company_id' => $owner->company->id,
        'user_id' => $applicant->id,
        'status' => $status,
        'job_title' => $job->title,
        'company_name' => $owner->company->name,
        'submitted_at' => now(),
    ]);
}
