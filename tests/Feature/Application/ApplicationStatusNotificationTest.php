<?php

use App\Services\ApplicationStatusService;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Shagalni\Shared\Enums\AccountStatus;
use Shagalni\Shared\Enums\ApplicationStatus;
use Shagalni\Shared\Models\User;
use Shagalni\Shared\Notifications\ApplicationStatusChanged;

beforeEach(function () {
    $this->withoutVite();

    $this->owner = approvedOwner();
    $this->applicant = User::factory()->create();
    $this->application = makeApplicationFor($this->owner, $this->applicant);
});

it('notifies the job seeker by in-app and email notification when the status changes', function () {
    Notification::fake();

    $this->actingAs($this->owner)
        ->patch(route('applications.status.update', $this->application), [
            'status' => 'under_review',
            'message' => 'We are reviewing your CV.',
        ])
        ->assertRedirect(route('applications.show', $this->application));

    Notification::assertSentTo(
        $this->applicant,
        ApplicationStatusChanged::class,
        function (ApplicationStatusChanged $notification, array $channels) {
            return $channels === ['database', 'mail']
                && $notification->status === 'under_review'
                && $notification->message === 'We are reviewing your CV.';
        }
    );
    Notification::assertSentToTimes($this->applicant, ApplicationStatusChanged::class, 1);
});

it('stores the owner message in the in-app notification', function () {
    $this->actingAs($this->owner)
        ->patch(route('applications.status.update', $this->application), [
            'status' => 'under_review',
            'message' => 'We are reviewing your CV.',
        ]);

    $notification = $this->applicant->notifications()->first();

    expect($notification)->not->toBeNull()
        ->and($notification->data['type'])->toBe('application_status_changed')
        ->and($notification->data['status'])->toBe('under_review')
        ->and($notification->data['status_message'])->toBe('We are reviewing your CV.');
});

it('sends the notification even when the owner sends no message', function () {
    Notification::fake();

    $this->actingAs($this->owner)
        ->patch(route('applications.status.update', $this->application), [
            'status' => 'under_review',
        ]);

    Notification::assertSentTo($this->applicant, ApplicationStatusChanged::class);
});

it('still changes the status but suppresses delivery when the job seeker cannot receive notifications', function (array $state) {
    Notification::fake();

    $this->applicant->forceFill($state)->save();

    $this->actingAs($this->owner)
        ->patch(route('applications.status.update', $this->application), [
            'status' => 'under_review',
            'message' => 'Hello',
        ]);

    Notification::assertNothingSent();

    expect($this->application->fresh()->status)->toBe(ApplicationStatus::UnderReview)
        ->and($this->application->statusHistories()->count())->toBe(1);
})->with([
    'suspended' => [['status' => AccountStatus::Suspended]],
    'deactivated' => [['status' => AccountStatus::Deactivated]],
    'deletion pending' => [['deletion_requested_at' => '2026-11-01 00:00:00']],
]);

it('keeps the committed status and history when sending the queued mail later fails', function () {
    Queue::fake();

    app(ApplicationStatusService::class)->updateStatus(
        $this->application,
        ApplicationStatus::UnderReview,
        $this->owner,
        'We are reviewing your CV.'
    );

    $mailJob = Queue::pushed(
        SendQueuedNotifications::class,
        fn (SendQueuedNotifications $job): bool => $job->notification instanceof ApplicationStatusChanged
            && $job->channels === ['mail']
    )->first();

    expect($mailJob)->not->toBeNull();

    $channelManager = Mockery::mock(ChannelManager::class);
    $channelManager->shouldReceive('sendNow')
        ->once()
        ->andThrow(new RuntimeException('Mail server unavailable.'));

    expect(fn () => $mailJob->handle($channelManager))
        ->toThrow(RuntimeException::class, 'Mail server unavailable.');

    expect($this->application->fresh()->status)->toBe(ApplicationStatus::UnderReview)
        ->and($this->application->statusHistories()->count())->toBe(1);
});

it('does not notify when the transition is rejected', function () {
    Notification::fake();

    $this->actingAs($this->owner)
        ->patch(route('applications.status.update', $this->application), [
            'status' => 'interview',
        ])
        ->assertSessionHasErrors('status');

    Notification::assertNothingSent();

    expect($this->application->fresh()->status)->toBe(ApplicationStatus::Pending);
});
