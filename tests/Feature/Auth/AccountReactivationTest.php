<?php

use App\Enums\AccountStatus;
use App\Models\User;
use App\Notifications\AccountReactivationRequested;
use App\Services\AccountLifecycleService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

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

test('the request page can be rendered', function () {
    $this->get(route('account-reactivation.create'))->assertOk();
});

test('a deactivated user receives a reactivation link', function () {
    Notification::fake();

    $user = User::factory()->deactivated()->create();

    $this->post(route('account-reactivation.store'), ['email' => $user->email])
        ->assertSessionHas('status');

    Notification::assertSentTo($user, AccountReactivationRequested::class);
});

test('no email is sent for active, suspended, anonymized or unknown accounts', function () {
    Notification::fake();

    $active = User::factory()->create();
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
    $user = User::factory()->deactivated()->create();

    $url = requestReactivationUrl($user);

    $this->get($url)->assertRedirect(route('login'));

    expect($user->fresh()->status)->toBe(AccountStatus::Active);
});

test('the link can only be used once', function () {
    $user = User::factory()->deactivated()->create();

    $url = requestReactivationUrl($user);

    $this->get($url);

    expect($user->fresh()->status)->toBe(AccountStatus::Active);

    // The user deactivates again, then tries the old link.
    $user->refresh()->forceFill(['status' => AccountStatus::Deactivated])->save();

    $this->get($url)->assertRedirect(route('login'));

    expect($user->fresh()->status)->toBe(AccountStatus::Deactivated);
});

test('a newer link replaces the older one', function () {
    $user = User::factory()->deactivated()->create();

    $oldUrl = requestReactivationUrl($user);
    requestReactivationUrl($user);

    $this->get($oldUrl);

    expect($user->fresh()->status)->toBe(AccountStatus::Deactivated);
});

test('the link expires after 24 hours', function () {
    $user = User::factory()->deactivated()->create();

    $url = requestReactivationUrl($user);

    $this->travel(25)->hours();

    $this->get($url)->assertForbidden();

    expect($user->fresh()->status)->toBe(AccountStatus::Deactivated);
});

test('a tampered link is rejected', function () {
    $user = User::factory()->deactivated()->create();

    $url = requestReactivationUrl($user);

    $this->get($url.'x')->assertForbidden();

    expect($user->fresh()->status)->toBe(AccountStatus::Deactivated);
});

test('a suspended account cannot use a reactivation link', function () {
    $user = User::factory()->suspended()->create();

    $token = app(AccountLifecycleService::class)->issueReactivationToken($user);

    $url = URL::temporarySignedRoute(
        'account-reactivation.confirm',
        now()->addHours(24),
        ['user' => $user->getRouteKey(), 'token' => $token]
    );

    $this->get($url)->assertRedirect(route('login'));

    expect($user->fresh()->status)->toBe(AccountStatus::Suspended);
});

test('an anonymized account cannot be reactivated even with a valid token', function () {
    $user = User::factory()->deactivated()->create(['anonymized_at' => now()]);

    $token = app(AccountLifecycleService::class)->issueReactivationToken($user);

    $url = URL::temporarySignedRoute(
        'account-reactivation.confirm',
        now()->addHours(24),
        ['user' => $user->getRouteKey(), 'token' => $token]
    );

    $this->get($url)->assertRedirect(route('login'));

    expect($user->fresh()->status)->toBe(AccountStatus::Deactivated);
});

test('a reactivated user can sign in again', function () {
    $user = User::factory()->deactivated()->create();

    $this->get(requestReactivationUrl($user));

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
});
