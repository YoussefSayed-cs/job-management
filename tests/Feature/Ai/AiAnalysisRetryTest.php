<?php

use Shagalni\Shared\Enums\AiAnalysisStatus;
use Shagalni\Shared\Enums\ApplicationStatus;
use Shagalni\Shared\Enums\UserRole;
use Shagalni\Shared\Jobs\ProcessResumeAnalysis;
use Shagalni\Shared\Models\AiAnalysis;
use Shagalni\Shared\Models\Application;
use Shagalni\Shared\Models\User;
use Illuminate\Support\Facades\Queue;

function failedAnalysisFor(Application $application, array $attributes = []): AiAnalysis
{
    return AiAnalysis::create(array_merge([
        'application_id' => $application->id,
        'status' => AiAnalysisStatus::Failed,
        'attempts' => 3,
        'failure_reason' => 'Service down',
    ], $attributes));
}

it('lets the owning company owner retry a failed analysis without duplicating records', function () {
    Queue::fake();
    $owner = approvedOwner();
    $application = makeApplicationFor($owner);
    $analysis = failedAnalysisFor($application);

    $this->from(route('applications.show', $application))
        ->actingAs($owner)
        ->post(route('ai-analyses.retry', $analysis))
        ->assertRedirect(route('applications.show', $application))
        ->assertSessionHas('status', 'The analysis has been queued again.');

    expect($analysis->fresh()->status)->toBe(AiAnalysisStatus::Pending)
        ->and($analysis->fresh()->failure_reason)->toBeNull()
        ->and($analysis->fresh()->attempts)->toBe(3)
        ->and(AiAnalysis::count())->toBe(1)
        ->and(Application::count())->toBe(1)
        ->and($application->fresh()->status)->toBe(ApplicationStatus::Pending);

    Queue::assertPushedOn('ai', ProcessResumeAnalysis::class);
});

it('lets an admin retry a failed analysis', function () {
    Queue::fake();
    $application = makeApplicationFor(approvedOwner());
    $analysis = failedAnalysisFor($application);

    $this->from(route('dashboard'))
        ->actingAs(adminUser())
        ->post(route('ai-analyses.retry', $analysis))
        ->assertRedirect(route('dashboard'));

    expect($analysis->fresh()->status)->toBe(AiAnalysisStatus::Pending)
        ->and($analysis->fresh()->failure_reason)->toBeNull()
        ->and($analysis->fresh()->attempts)->toBe(3)
        ->and(AiAnalysis::count())->toBe(1)
        ->and(Application::count())->toBe(1);

    Queue::assertPushedOn('ai', ProcessResumeAnalysis::class);
});

it('denies a different company owner from retrying a failed analysis', function () {
    Queue::fake();
    $application = makeApplicationFor(approvedOwner());
    $analysis = failedAnalysisFor($application);

    $this->actingAs(approvedOwner())
        ->post(route('ai-analyses.retry', $analysis))
        ->assertForbidden();

    expect($analysis->fresh()->status)->toBe(AiAnalysisStatus::Failed)
        ->and($analysis->fresh()->failure_reason)->toBe('Service down');

    Queue::assertNothingPushed();
});

it('denies a job seeker from retrying a failed analysis', function () {
    Queue::fake();
    $application = makeApplicationFor(approvedOwner());
    $analysis = failedAnalysisFor($application);
    $jobSeeker = User::factory()->create(['role' => UserRole::JobSeeker]);

    $this->actingAs($jobSeeker)
        ->post(route('ai-analyses.retry', $analysis))
        ->assertForbidden();

    expect($analysis->fresh()->status)->toBe(AiAnalysisStatus::Failed);
    Queue::assertNothingPushed();
});

it('redirects guests to login when they try to retry an analysis', function () {
    $analysis = failedAnalysisFor(makeApplicationFor(approvedOwner()));

    $this->post(route('ai-analyses.retry', $analysis))
        ->assertRedirect(route('login'));
});

it('rejects retries unless the analysis is failed', function (AiAnalysisStatus $status) {
    Queue::fake();
    $application = makeApplicationFor(approvedOwner());
    $analysis = AiAnalysis::create([
        'application_id' => $application->id,
        'status' => $status,
        'attempts' => 2,
    ]);

    $this->actingAs($application->company->owner)
        ->post(route('ai-analyses.retry', $analysis))
        ->assertForbidden();

    expect($analysis->fresh()->status)->toBe($status);
    Queue::assertNothingPushed();
})->with([
    'pending' => AiAnalysisStatus::Pending,
    'processing' => AiAnalysisStatus::Processing,
    'completed' => AiAnalysisStatus::Completed,
]);

it('queues only once when a second retry is submitted after the first', function () {
    Queue::fake();
    $owner = approvedOwner();
    $analysis = failedAnalysisFor(makeApplicationFor($owner));

    $this->actingAs($owner)
        ->post(route('ai-analyses.retry', $analysis))
        ->assertRedirect();

    $this->actingAs($owner)
        ->post(route('ai-analyses.retry', $analysis))
        ->assertForbidden();

    expect(Queue::pushed(ProcessResumeAnalysis::class))->toHaveCount(1);
});

it('does not change the application status when retrying an analysis', function () {
    Queue::fake();
    $owner = approvedOwner();
    $application = makeApplicationFor($owner, null, ApplicationStatus::Accepted);
    $analysis = failedAnalysisFor($application);

    $this->actingAs($owner)
        ->post(route('ai-analyses.retry', $analysis))
        ->assertRedirect();

    expect($application->fresh()->status)->toBe(ApplicationStatus::Accepted);
});

it('lists only failed analyses and omits stored score and feedback for admins', function () {
    $owner = approvedOwner();
    $failedApplication = makeApplicationFor($owner);
    $failedApplication->update([
        'job_title' => 'Failed Backend Role',
        'company_name' => 'Failed Company Name',
    ]);
    failedAnalysisFor($failedApplication, [
        'match_score' => 71,
        'feedback' => 'SECRET-FEEDBACK',
    ]);

    $completedApplication = makeApplicationFor($owner);
    $completedApplication->update(['job_title' => 'Hidden Completed Role']);
    AiAnalysis::create([
        'application_id' => $completedApplication->id,
        'status' => AiAnalysisStatus::Completed,
        'attempts' => 1,
    ]);

    $this->actingAs(adminUser())
        ->get(route('ai-analyses.failed'))
        ->assertOk()
        ->assertSee('Failed Backend Role')
        ->assertSee('Failed Company Name')
        ->assertSee('Service down')
        ->assertDontSee('Hidden Completed Role')
        ->assertDontSee('71%')
        ->assertDontSee('SECRET-FEEDBACK');
});

it('denies company owners and job seekers from the failed analysis page and redirects guests', function () {
    $this->actingAs(approvedOwner())
        ->get(route('ai-analyses.failed'))
        ->assertForbidden();

    $this->actingAs(User::factory()->create(['role' => UserRole::JobSeeker]))
        ->get(route('ai-analyses.failed'))
        ->assertForbidden();

    auth()->logout();

    $this->get(route('ai-analyses.failed'))
        ->assertRedirect(route('login'));
});

it('continues to deny admins access to application details', function () {
    $application = makeApplicationFor(approvedOwner());

    $this->actingAs(adminUser())
        ->get(route('applications.show', $application))
        ->assertForbidden();
});

it('shows the retry button to the owner only for failed analyses', function () {
    $owner = approvedOwner();
    $failedApplication = makeApplicationFor($owner);
    failedAnalysisFor($failedApplication);

    $this->actingAs($owner)
        ->get(route('applications.show', $failedApplication))
        ->assertOk()
        ->assertSee('Retry analysis');

    $pendingApplication = makeApplicationFor($owner);
    AiAnalysis::create([
        'application_id' => $pendingApplication->id,
        'status' => AiAnalysisStatus::Pending,
        'attempts' => 0,
    ]);

    $this->actingAs($owner)
        ->get(route('applications.show', $pendingApplication))
        ->assertOk()
        ->assertDontSee('Retry analysis')
        ->assertSee('The analysis is in progress. Refresh the page in a moment.');

    $completedApplication = makeApplicationFor($owner);
    AiAnalysis::create([
        'application_id' => $completedApplication->id,
        'status' => AiAnalysisStatus::Completed,
        'attempts' => 1,
    ]);

    $this->actingAs($owner)
        ->get(route('applications.show', $completedApplication))
        ->assertOk()
        ->assertDontSee('Retry analysis')
        ->assertDontSee('The analysis is in progress. Refresh the page in a moment.');
});

it('paginates failed analyses with one row on page two when there are sixteen', function () {
    $owner = approvedOwner();

    for ($index = 1; $index <= 16; $index++) {
        failedAnalysisFor(makeApplicationFor($owner));
    }

    $response = $this->actingAs(adminUser())
        ->get(route('ai-analyses.failed', ['page' => 2]))
        ->assertOk();

    expect($response->viewData('analyses')->total())->toBe(16)
        ->and($response->viewData('analyses')->count())->toBe(1);
});