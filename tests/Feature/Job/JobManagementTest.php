<?php

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Company;
use App\Models\JobCategory;
use App\Models\JobVacancy;
use App\Models\User;

beforeEach(function () {
    $this->owner = approvedOwner();

    $this->category = JobCategory::create([
        'name' => 'Software',
    ]);

    $this->makeJob = function (
        Company $company,
        array $attributes = []
    ): JobVacancy {
        return $company->jobVacancies()->create(array_merge([
            'job_category_id' => $this->category->id,
            'title' => 'Laravel Developer',
            'description' => 'Build Laravel applications.',
            'requirements' => 'PHP and Laravel experience.',
            'location' => 'Cairo',
            'employment_type' => 'full_time',
            'work_location_type' => 'hybrid',
            'required_skills' => 'PHP, Laravel, MySQL',
            'salary_min' => 10000,
            'salary_max' => 20000,
            'experience_level' => 'junior',
            'application_deadline' => now()->addDays(20)->toDateString(),
            'status' => JobStatus::Active,
        ], $attributes));
    };
});

it('allows a company owner to create and publish a job', function () {
    $response = $this->actingAs($this->owner)->post(route('jobs.store'), [
        'job_category_id' => $this->category->id,
        'title' => 'Backend Developer',
        'description' => 'Build APIs.',
        'requirements' => 'Laravel experience.',
        'location' => 'Cairo',
        'employment_type' => 'full_time',
        'work_location_type' => 'remote',
        'required_skills' => 'PHP, Laravel',
        'salary_min' => 10000,
        'salary_max' => 20000,
        'experience_level' => 'junior',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('job_vacancies', [
        'company_id' => $this->owner->company->id,
        'title' => 'Backend Developer',
        'status' => JobStatus::Active->value,
    ]);
});

it('does not allow an admin to create jobs', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->get(route('jobs.create'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->post(route('jobs.store'), [])
        ->assertForbidden();
});

it('does not allow a job seeker to access job management', function () {
    $jobSeeker = User::factory()->create([
        'role' => UserRole::JobSeeker,
    ]);

    $this->actingAs($jobSeeker)
        ->get(route('jobs.index'))
        ->assertForbidden();
});

it('assigns the authenticated owners company and ignores client supplied status', function () {
    $otherOwner = approvedOwner();

    $response = $this->actingAs($this->owner)->post(route('jobs.store'), [
        'company_id' => $otherOwner->company->id,
        'status' => JobStatus::Archived->value,
        'archived_at' => now()->toDateTimeString(),

        'job_category_id' => $this->category->id,
        'title' => 'Secure Backend Developer',
        'description' => 'Build secure APIs.',
        'requirements' => 'PHP experience.',
        'location' => 'Cairo',
        'employment_type' => 'full_time',
        'work_location_type' => 'on_site',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('job_vacancies', [
        'company_id' => $this->owner->company->id,
        'title' => 'Secure Backend Developer',
        'status' => JobStatus::Active->value,
        'archived_at' => null,
    ]);
});

it('lists only jobs belonging to the authenticated company owner', function () {
    ($this->makeJob)($this->owner->company, [
        'title' => 'My Company Job',
    ]);

    $otherOwner = approvedOwner();

    ($this->makeJob)($otherOwner->company, [
        'title' => 'Other Company Job',
    ]);

    $response = $this->actingAs($this->owner)
        ->get(route('jobs.index'));

    $response->assertOk()
        ->assertSee('My Company Job')
        ->assertDontSee('Other Company Job');
});

it('allows admin to view jobs from all companies', function () {
    $admin = adminUser();

    ($this->makeJob)($this->owner->company, [
        'title' => 'First Company Job',
    ]);

    $otherOwner = approvedOwner();

    ($this->makeJob)($otherOwner->company, [
        'title' => 'Second Company Job',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('jobs.index'));

    $response->assertOk()
        ->assertSee('First Company Job')
        ->assertSee('Second Company Job');
});

it('prevents a company owner from viewing another company job', function () {
    $otherOwner = approvedOwner();

    $job = ($this->makeJob)($otherOwner->company);

    $this->actingAs($this->owner)
        ->get(route('jobs.show', $job))
        ->assertForbidden();
});

it('prevents a company owner from editing another company job', function () {
    $otherOwner = approvedOwner();

    $job = ($this->makeJob)($otherOwner->company);

    $this->actingAs($this->owner)
        ->get(route('jobs.edit', $job))
        ->assertForbidden();
});

it('allows an owner to update job fields without changing company or lifecycle status', function () {
    $job = ($this->makeJob)($this->owner->company);

    $otherOwner = approvedOwner();

    $response = $this->actingAs($this->owner)
        ->put(route('jobs.update', $job), [
            'company_id' => $otherOwner->company->id,
            'status' => JobStatus::Archived->value,
            'archived_at' => now()->toDateTimeString(),

            'job_category_id' => $this->category->id,
            'title' => 'Updated Laravel Developer',
            'description' => 'Updated description.',
            'requirements' => 'Updated requirements.',
            'location' => 'Alexandria',
            'employment_type' => 'part_time',
            'work_location_type' => 'remote',
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('job_vacancies', [
        'id' => $job->id,
        'company_id' => $this->owner->company->id,
        'title' => 'Updated Laravel Developer',
        'location' => 'Alexandria',
        'status' => JobStatus::Active->value,
        'archived_at' => null,
    ]);
});

it('rejects invalid salary ranges', function () {
    $response = $this->actingAs($this->owner)
        ->post(route('jobs.store'), [
            'job_category_id' => $this->category->id,
            'title' => 'Developer',
            'description' => 'Description.',
            'requirements' => 'Requirements.',
            'location' => 'Cairo',
            'employment_type' => 'full_time',
            'work_location_type' => 'remote',
            'salary_min' => 20000,
            'salary_max' => 10000,
        ]);

    $response->assertSessionHasErrors('salary_max');
});

it('closes active jobs and reopens closed jobs', function () {
    $job = ($this->makeJob)($this->owner->company);

    $this->actingAs($this->owner)
        ->post(route('jobs.close', $job))
        ->assertRedirect();

    $this->assertDatabaseHas('job_vacancies', [
        'id' => $job->id,
        'status' => JobStatus::Closed->value,
    ]);

    $this->actingAs($this->owner)
        ->post(route('jobs.reopen', $job))
        ->assertRedirect();

    $this->assertDatabaseHas('job_vacancies', [
        'id' => $job->id,
        'status' => JobStatus::Active->value,
    ]);
});

it('does not allow closing an already closed job', function () {
    $job = ($this->makeJob)($this->owner->company, [
        'status' => JobStatus::Closed,
    ]);

    $this->actingAs($this->owner)
        ->post(route('jobs.close', $job))
        ->assertSessionHasErrors('status');
});

it('archives active jobs and restores them within 30 days', function () {
    $job = ($this->makeJob)($this->owner->company);

    $this->actingAs($this->owner)
        ->post(route('jobs.archive', $job))
        ->assertRedirect();

    $this->assertDatabaseHas('job_vacancies', [
        'id' => $job->id,
        'status' => JobStatus::Archived->value,
    ]);

    $job->refresh();

    expect($job->archived_at)->not->toBeNull();

    $this->actingAs($this->owner)
        ->post(route('jobs.restore', $job))
        ->assertRedirect();

    $this->assertDatabaseHas('job_vacancies', [
        'id' => $job->id,
        'status' => JobStatus::Active->value,
        'archived_at' => null,
    ]);
});

it('does not restore a job after the 30-day grace period', function () {
    $job = ($this->makeJob)($this->owner->company, [
        'status' => JobStatus::Archived,
        'archived_at' => now()->subDays(31),
    ]);

    $this->actingAs($this->owner)
        ->post(route('jobs.restore', $job))
        ->assertSessionHasErrors('status');

    $this->assertDatabaseHas('job_vacancies', [
        'id' => $job->id,
        'status' => JobStatus::Archived->value,
    ]);
});

it('does not allow permanent deletion of a non archived job', function () {
    $job = ($this->makeJob)($this->owner->company);

    $this->actingAs($this->owner)
        ->delete(route('jobs.destroy', $job), [
            'confirmation' => 'yes',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('job_vacancies', [
        'id' => $job->id,
    ]);
});

it('permanently deletes archived jobs after explicit confirmation', function () {
    $job = ($this->makeJob)($this->owner->company, [
        'status' => JobStatus::Archived,
        'archived_at' => now(),
    ]);

    $this->actingAs($this->owner)
        ->delete(route('jobs.destroy', $job), [
            'confirmation' => 'yes',
        ])
        ->assertRedirect(route('jobs.index'));

    $this->assertDatabaseMissing('job_vacancies', [
        'id' => $job->id,
    ]);
});

it('preserves application history when a job is permanently deleted', function () {
    $job = ($this->makeJob)($this->owner->company, [
        'status' => JobStatus::Archived,
        'archived_at' => now(),
    ]);

    $jobSeeker = User::factory()->create([
        'role' => UserRole::JobSeeker,
    ]);

    $application = Application::create([
        'job_vacancy_id' => $job->id,
        'company_id' => $this->owner->company->id,
        'user_id' => $jobSeeker->id,
        'status' => ApplicationStatus::Pending,
        'job_title' => $job->title,
        'company_name' => $this->owner->company->name,
        'submitted_at' => now(),
    ]);

    $this->actingAs($this->owner)
        ->delete(route('jobs.destroy', $job), [
            'confirmation' => 'yes',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('applications', [
        'id' => $application->id,
        'job_vacancy_id' => null,
        'company_id' => $this->owner->company->id,
    ]);
});

it('automatically purges archived jobs after 30 days', function () {
    $job = ($this->makeJob)($this->owner->company, [
        'status' => JobStatus::Archived,
        'archived_at' => now()->subDays(31),
    ]);

    $jobSeeker = User::factory()->create([
        'role' => UserRole::JobSeeker,
    ]);

    $application = Application::create([
        'job_vacancy_id' => $job->id,
        'company_id' => $this->owner->company->id,
        'user_id' => $jobSeeker->id,
        'status' => ApplicationStatus::Pending,
        'job_title' => $job->title,
        'company_name' => $this->owner->company->name,
        'submitted_at' => now()->subDays(31),
    ]);

    $this->artisan('jobs:purge-archived')
        ->assertSuccessful();

    $this->assertDatabaseMissing('job_vacancies', [
        'id' => $job->id,
    ]);

    $this->assertDatabaseHas('applications', [
        'id' => $application->id,
        'job_vacancy_id' => null,
    ]);
});

it('does not purge archived jobs before the 30-day period expires', function () {
    $job = ($this->makeJob)($this->owner->company, [
        'status' => JobStatus::Archived,
        'archived_at' => now()->subDays(29),
    ]);

    $this->artisan('jobs:purge-archived')
        ->assertSuccessful();

    $this->assertDatabaseHas('job_vacancies', [
        'id' => $job->id,
    ]);
});