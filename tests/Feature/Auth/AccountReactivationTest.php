<?php

use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Shagalni\Shared\Enums\AccountStatus;
use Shagalni\Shared\Enums\RegistrationStatus;
use Shagalni\Shared\Enums\UserRole;
use Shagalni\Shared\Models\User;
use Shagalni\Shared\Notifications\AccountReactivationRequested;

beforeEach(function () {
    $this->withoutVite();
});

/**
 * Ask for a link and return the URL that was emailed.
 */
function requestReactivationUrl(User $user): string
{
    Notification::fake();

    test()->post(route('account-reactivation.store'), ['email' => $user->email]);

    $url = null;

    Notification::assertSentTo($user, AccountReactivationRequested::class, function ($notification) use ($user, &$url) {
        $url = $notification->toMail($user)->actionUrl;

        return true;
    });

    return $url ?? '';
}

function approvedReactivationOwner(array $attributes = []): User
{
    $owner = User::factory()->create(array_merge([
        'role' => UserRole::CompanyOwner,
    ], $attributes));

    createCompanyRecord([
        'owner_id' => $owner->id,
        'name' => 'Approved Co',
        'industry' => 'Software',
        'location' => 'Cairo',
        'registration_status' => RegistrationStatus::Approved,
    ]);

    return $owner;
}

test('the request page can be rendered', function () {
    $this->get(route('account-reactivation.create'))->assertOk();
});

test('a deactivated user receives a reactivation link', function () {
    Notification::fake();

    $user = approvedReactivationOwner(['status' => AccountStatus::Deactivated]);

    $this->post(route('account-reactivation.store'), ['email' => $user->email])
        ->assertSessionHas('status');

    Notification::assertSentTo($user, AccountReactivationRequested::class);
});

test('job seekers and unapproved company owners use the Job Seeker Portal reactivation flow', function () {
    Notification::fake();

    $jobSeeker = User::factory()->deactivated()->create();
    $pendingOwner = User::factory()->deactivated()->create([
        'role' => UserRole::CompanyOwner,
    ]);
    createCompanyRecord([
        'owner_id' => $pendingOwner->id,
        'name' => 'Pending Co',
        'industry' => 'Software',
        'location' => 'Cairo',
        'registration_status' => RegistrationStatus::PendingApproval,
    ]);

    foreach ([$jobSeeker, $pendingOwner] as $user) {
        $this->post(route('account-reactivation.store'), ['email' => $user->email])
            ->assertSessionHas('status');
    }

    Notification::assertNothingSent();
});

test('reactivation notification is queued with three attempts', function () {
    Queue::fake();

    $user = approvedReactivationOwner(['status' => AccountStatus::Deactivated]);

    $this->post(route('account-reactivation.store'), ['email' => $user->email])
        ->assertSessionHas('status');

    $jobs = Queue::pushed(
        SendQueuedNotifications::class,
        fn (SendQueuedNotifications $job): bool => $job->notification instanceof AccountReactivationRequested
    );

    expect($jobs)->toHaveCount(1)
        ->and($jobs->first()->tries)->toBe(3)
        ->and($jobs->first()->channels)->toBe(['mail']);
});

test('no email is sent for active, suspended, anonymized or unknown accounts', function () {
    Notification::fake();

    $active = approvedReactivationOwner();
    $suspended = User::factory()->suspended()->create();
    $tombstone = User::factory()->deactivated()->create(['anonymized_at' => now()]);

    foreach ([$active->email, $suspended->email, $tombstone->email, 'nobody@example.com'] as $email) {
        // Same answer for everyone, so the form does not reveal which emails exist.
        $this->post(route('account-reactivation.store'), ['email' => $email])
            ->assertSessionHas('status');
    }

    Notification::assertNothingSent();
});

test('the link reactivates the account', function () {
    $user = approvedReactivationOwner(['status' => AccountStatus::Deactivated]);

    $url = requestReactivationUrl($user);

    $this->get($url)
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Your account has been reactivated. You can sign in now.');

    expect($user->fresh()->status)->toBe(AccountStatus::Active);
});

test('the link warns about a pending deletion request after reactivation', function () {
    $user = approvedReactivationOwner([
        'status' => AccountStatus::Deactivated,
        'deletion_requested_at' => now(),
    ]);

    $url = requestReactivationUrl($user);

    $this->get($url)
        ->assertRedirect(route('login'))
        ->assertSessionHas(
            'status',
            'Your account has been reactivated, but it still has a pending deletion request. Cancel the deletion using the link in your email before signing in.'
        );
});

test('the link can only be used once', function () {
    $user = approvedReactivationOwner(['status' => AccountStatus::Deactivated]);

    $url = requestReactivationUrl($user);

    $this->get($url);

    expect($user->fresh()->status)->toBe(AccountStatus::Active);

    // The user deactivates again, then tries the old link.
    $user->refresh()->forceFill(['status' => AccountStatus::Deactivated])->save();

    $this->get($url)->assertRedirect(route('login'));

    expect($user->fresh()->status)->toBe(AccountStatus::Deactivated);
});

test('a newer link replaces the older one', function () {
    $user = approvedReactivationOwner(['status' => AccountStatus::Deactivated]);

    $oldUrl = requestReactivationUrl($user);
    requestReactivationUrl($user);

    $this->get($oldUrl);

    expect($user->fresh()->status)->toBe(AccountStatus::Deactivated);
});

test('the link expires after 24 hours', function () {
    $user = approvedReactivationOwner(['status' => AccountStatus::Deactivated]);

    $url = requestReactivationUrl($user);

    $this->travel(25)->hours();

    $this->get($url)->assertForbidden();

    expect($user->fresh()->status)->toBe(AccountStatus::Deactivated);
});

test('a tampered link is rejected', function () {
    $user = approvedReactivationOwner(['status' => AccountStatus::Deactivated]);

    $url = requestReactivationUrl($user);

    $this->get($url.'x')->assertForbidden();

    expect($user->fresh()->status)->toBe(AccountStatus::Deactivated);
});

test('a suspended account cannot use a reactivation link', function () {
    $user = approvedReactivationOwner(['status' => AccountStatus::Suspended]);

    $token = Str::random(64);
    Cache::put('account-reactivation:'.$user->id, $token, now()->addHours(24));

    $url = URL::temporarySignedRoute(
        'account-reactivation.confirm',
        now()->addHours(24),
        ['user' => $user->getRouteKey(), 'token' => $token]
    );

    $this->get($url)->assertRedirect(route('login'));

    expect($user->fresh()->status)->toBe(AccountStatus::Suspended);
});

test('an anonymized account cannot be reactivated even with a valid token', function () {
    $user = approvedReactivationOwner([
        'status' => AccountStatus::Deactivated,
        'anonymized_at' => now(),
    ]);

    $token = Str::random(64);
    Cache::put('account-reactivation:'.$user->id, $token, now()->addHours(24));

    $url = URL::temporarySignedRoute(
        'account-reactivation.confirm',
        now()->addHours(24),
        ['user' => $user->getRouteKey(), 'token' => $token]
    );

    $this->get($url)->assertRedirect(route('login'));

    expect($user->fresh()->status)->toBe(AccountStatus::Deactivated);
});

test('a reactivated user can sign in again', function () {
    $user = approvedReactivationOwner(['status' => AccountStatus::Deactivated]);

    $this->get(requestReactivationUrl($user));

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
});
