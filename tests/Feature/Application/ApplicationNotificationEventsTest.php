<?php

use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Shagalni\Shared\Enums\ApplicationStatus;
use Shagalni\Shared\Events\ApplicationStatusUpdated;
use Shagalni\Shared\Events\ApplicationSubmitted;
use Shagalni\Shared\Events\ApplicationWithdrawn;
use Shagalni\Shared\Listeners\NotifyCompanyOwnerOfNewApplication;
use Shagalni\Shared\Listeners\NotifyCompanyOwnerOfWithdrawal;
use Shagalni\Shared\Models\AiAnalysis;
use Shagalni\Shared\Models\ApplicationStatusHistory;
use Shagalni\Shared\Notifications\ApplicationWithdrawnNotice;
use Shagalni\Shared\Notifications\NewApplicationReceived;
use Shagalni\Shared\Observers\ApplicationStatusHistoryObserver;

test('initial application history does not dispatch a status event', function () {
    Event::fake();

    $history = new ApplicationStatusHistory([
        'from_status' => null,
        'to_status' => ApplicationStatus::Pending,
    ]);

    app(ApplicationStatusHistoryObserver::class)->created($history);

    Event::assertNothingDispatched();
});

test('status history dispatches the matching status or withdrawal event', function () {
    Event::fake();

    $observer = app(ApplicationStatusHistoryObserver::class);
    $updated = new ApplicationStatusHistory([
        'from_status' => ApplicationStatus::Pending,
        'to_status' => ApplicationStatus::UnderReview,
    ]);
    $withdrawn = new ApplicationStatusHistory([
        'from_status' => ApplicationStatus::Pending,
        'to_status' => ApplicationStatus::Withdrawn,
    ]);

    $observer->created($updated);
    Event::assertDispatched(ApplicationStatusUpdated::class);

    $observer->created($withdrawn);
    Event::assertDispatched(ApplicationWithdrawn::class);
});

test('withdrawal notifies only the owner of the application company', function () {
    Notification::fake();

    $owner = approvedOwner();
    $otherOwner = approvedOwner();
    $application = makeApplicationFor($owner);
    $history = ApplicationStatusHistory::create([
        'application_id' => $application->id,
        'from_status' => ApplicationStatus::Pending,
        'to_status' => ApplicationStatus::Withdrawn,
        'message' => 'I accepted another role.',
    ]);

    app(NotifyCompanyOwnerOfWithdrawal::class)->handle(new ApplicationWithdrawn($history));

    Notification::assertSentTo($owner, ApplicationWithdrawnNotice::class, function ($notification, array $channels) use ($application) {
        return $channels === ['database', 'mail']
            && $notification->applicationId === $application->id
            && $notification->status === ApplicationStatus::Withdrawn->value
            && $notification->message === 'I accepted another role.';
    });
    Notification::assertNotSentTo($otherOwner, ApplicationWithdrawnNotice::class);
});

test('new application notifies its company owner independently of AI analysis outcome', function () {
    Notification::fake();

    $owner = approvedOwner();
    $otherOwner = approvedOwner();
    $application = makeApplicationFor($owner);

    AiAnalysis::create([
        'application_id' => $application->id,
        'status' => 'failed',
        'attempts' => 3,
        'failure_reason' => 'Provider unavailable.',
    ]);

    $payload = null;
    app(NotifyCompanyOwnerOfNewApplication::class)->handle(new ApplicationSubmitted($application));

    Notification::assertSentTo($owner, NewApplicationReceived::class, function ($notification, array $channels) use ($application, $owner, &$payload) {
        $payload = $notification->toArray($owner);

        return $channels === ['database', 'mail']
            && $notification->applicationId === $application->id
            && $notification->jobTitle === $application->job_title
            && $notification->status === ApplicationStatus::Pending->value;
    });
    Notification::assertNotSentTo($otherOwner, NewApplicationReceived::class);

    expect(array_keys($payload))->toBe([
        'application_id',
        'job_title',
        'applicant_name',
        'status',
        'message',
    ])->and($payload['message'])->toBe('A new application has been submitted.');
});

test('company owner application notifications queue database and email channels with retries', function () {
    Queue::fake();

    $owner = approvedOwner();
    $application = makeApplicationFor($owner);
    $history = new ApplicationStatusHistory([
        'application_id' => $application->id,
        'from_status' => ApplicationStatus::Pending,
        'to_status' => ApplicationStatus::Withdrawn,
    ]);
    $history->setRelation('application', $application);

    app(NotifyCompanyOwnerOfNewApplication::class)->handle(new ApplicationSubmitted($application));
    app(NotifyCompanyOwnerOfWithdrawal::class)->handle(new ApplicationWithdrawn($history));

    $jobs = Queue::pushed(
        SendQueuedNotifications::class,
        fn (SendQueuedNotifications $job): bool => in_array(
            $job->notification::class,
            [NewApplicationReceived::class, ApplicationWithdrawnNotice::class],
            true
        )
    );

    expect($jobs)->toHaveCount(4)
        ->and($jobs->pluck('channels')->all())->toContain(['database'], ['mail'])
        ->and($jobs->every(fn (SendQueuedNotifications $job): bool => $job->tries === 3))->toBeTrue();
});
