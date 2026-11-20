<?php

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Shagalni\Shared\Models\Application;
use Shagalni\Shared\Models\User;
use Shagalni\Shared\Notifications\NewApplicationReceived;

beforeEach(function () {
    $this->withoutVite();
});

function createNotificationCenterNotification(User $owner, string $jobTitle = 'Notification Test Job'): DatabaseNotification
{
    $application = makeApplicationFor($owner);
    $application->update(['job_title' => $jobTitle]);

    $owner->notify(NewApplicationReceived::forApplication($application));

    return $owner->notifications()->latest()->firstOrFail();
}

function createNotificationCenterUrlNotification(User $owner, string $url): DatabaseNotification
{
    return $owner->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'test-notification',
        'data' => ['url' => $url],
    ]);
}

it('shows only the authenticated owners notifications', function () {
    $owner = approvedOwner();
    $otherOwner = approvedOwner();

    createNotificationCenterNotification($owner, 'My Private Notification');
    createNotificationCenterNotification($otherOwner, 'Another Owners Notification');

    $this->actingAs($owner)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('My Private Notification')
        ->assertDontSee('Another Owners Notification');
});

it('cannot mark another users notification as read', function () {
    $owner = approvedOwner();
    $otherOwner = approvedOwner();
    $notification = createNotificationCenterNotification($otherOwner);

    $this->actingAs($owner)
        ->post(route('notifications.read', $notification->id))
        ->assertNotFound();

    expect($notification->fresh()->read_at)->toBeNull();
});

it('marks its own notification as read and redirects to its application', function () {
    $owner = approvedOwner();
    $notification = createNotificationCenterNotification($owner);
    $applicationId = $notification->data['application_id'];

    $this->actingAs($owner)
        ->post(route('notifications.read', $notification->id))
        ->assertRedirect(route('applications.show', $applicationId));

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('marks only the authenticated users unread notifications as read', function () {
    $owner = approvedOwner();
    $otherOwner = approvedOwner();

    createNotificationCenterNotification($owner, 'First Own Notification');
    createNotificationCenterNotification($owner, 'Second Own Notification');
    createNotificationCenterNotification($otherOwner, 'Other Owners Notification');

    $this->actingAs($owner)
        ->from(route('notifications.index'))
        ->post(route('notifications.read-all'))
        ->assertRedirect(route('notifications.index'))
        ->assertSessionHas('status', 'All notifications marked as read.');

    expect($owner->unreadNotifications()->count())->toBe(0)
        ->and($otherOwner->unreadNotifications()->count())->toBe(1);
});

it('returns to the notification list when its application no longer exists', function () {
    $owner = approvedOwner();
    $notification = createNotificationCenterNotification($owner);

    Application::whereKey($notification->data['application_id'])->delete();

    $this->actingAs($owner)
        ->post(route('notifications.read', $notification->id))
        ->assertRedirect(route('notifications.index'));

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('allows same-host notification URLs and rejects external redirect URLs', function () {
    $owner = approvedOwner();
    $external = createNotificationCenterUrlNotification($owner, 'https://other.example/path');
    $sameHost = createNotificationCenterUrlNotification($owner, route('dashboard'));

    $this->actingAs($owner)
        ->post(route('notifications.read', $external->id))
        ->assertRedirect(route('notifications.index'));

    $this->actingAs($owner)
        ->post(route('notifications.read', $sameHost->id))
        ->assertRedirect(route('dashboard'));
});

it('requires authentication for all notification routes', function () {
    $this->get(route('notifications.index'))
        ->assertRedirect(route('login'));

    $this->post(route('notifications.read-all'))
        ->assertRedirect(route('login'));

    $this->post(route('notifications.read', (string) Str::uuid()))
        ->assertRedirect(route('login'));
});

it('shows the unread count in the sidebar and notification page', function () {
    $owner = approvedOwner();

    createNotificationCenterNotification($owner, 'Unread Notification One');
    createNotificationCenterNotification($owner, 'Unread Notification Two');

    $this->actingAs($owner)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Unread Notification One')
        ->assertSee('Unread Notification Two')
        ->assertSee('>2</span>', false);
});
