<?php

use Shagalni\Shared\Enums\AiAnalysisStatus;
use Shagalni\Shared\Enums\ApplicationStatus;
use Shagalni\Shared\Models\AiAnalysis;
use Shagalni\Shared\Models\Application;
use Shagalni\Shared\Models\JobCategory;
use Shagalni\Shared\Models\User;

it('renders message templates when an approved owner reviews an active application', function () {
    $owner = approvedOwner();
    $applicant = User::factory()->create();
    $category = JobCategory::create([
        'name' => 'Software',
    ]);
    $job = $owner->company->jobVacancies()->create([
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
        ->assertSee('Your application is currently under review.');
});

it('shows only sequential statuses and rejection for a pending application', function () {
    $owner = approvedOwner();
    $application = makeApplicationFor($owner);

    $this->actingAs($owner)
        ->get(route('applications.show', $application))
        ->assertOk()
        ->assertSee('<option value="under_review"', false)
        ->assertSee('<option value="rejected"', false)
        ->assertDontSee('<option value="interview"', false)
        ->assertDontSee('<option value="withdrawn"', false);
});

it('shows an owner the AI analysis for an application submitted to their company', function () {
    $owner = approvedOwner();
    $application = makeApplicationFor($owner);

    AiAnalysis::create([
        'application_id' => $application->id,
        'status' => AiAnalysisStatus::Completed,
        'match_score' => 82,
        'feedback' => 'Strong technical experience and relevant delivery history.',
        'attempts' => 1,
        'analyzed_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('applications.index'))
        ->assertOk()
        ->assertSee('AI Analysis')
        ->assertSee('Completed')
        ->assertSee('Match score: 82%');

    $this->actingAs($owner)
        ->get(route('applications.show', $application))
        ->assertOk()
        ->assertSee('AI Resume Analysis')
        ->assertSee('Analysis Status')
        ->assertSee('Match Score')
        ->assertSee('82%')
        ->assertSee('Strong technical experience and relevant delivery history.');
});
