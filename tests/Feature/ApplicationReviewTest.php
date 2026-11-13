<?php

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\JobCategory;
use App\Models\JobVacancy;
use App\Models\User;

it('renders message templates when an approved owner reviews an active application', function () {
    $owner = approvedOwner();
    $applicant = User::factory()->create();
    $category = JobCategory::create([
        'name' => 'Software',
    ]);
    $job = JobVacancy::create([
        'company_id' => $owner->company->id,
        'job_category_id' => $category->id,
        'title' => 'Backend Developer',
        'description' => 'Build backend services.',
        'requirements' => 'PHP experience.',
        'location' => 'Cairo',
        'employment_type' => 'full_time',
        'work_location_type' => 'on_site',
    ]);
    $application = Application::create([
        'job_vacancy_id' => $job->id,
        'company_id' => $owner->company->id,
        'user_id' => $applicant->id,
        'status' => ApplicationStatus::Pending,
        'job_title' => $job->title,
        'company_name' => $owner->company->name,
        'submitted_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('applications.show', $application))
        ->assertOk()
        ->assertSee('data-status="under_review"', false)
        ->assertSee('Your application is now under review.');
});
