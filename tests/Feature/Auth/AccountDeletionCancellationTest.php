<?php

use App\Enums\AccountStatus;
use App\Models\User;
use App\Notifications\AccountDeletionRequested;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    $this->withoutVite();
});

/**
 * The URL that would be emailed for the user's current deletion request.
 */
function deletionCancelUrl(User $user): string
{
    return (new AccountDeletionRequested)->toMail($user)->actionUrl;
}

test('the link cancels the deletion request', function () {
    $user = User::factory()->create(['deletion_requested_at' => now()]);

    $this->get(deletionCancelUrl($user))
        ->assertRedirect(route('login'))
        ->assertSessionHas('status');

    expect($user->fresh()->deletion_requested_at)->toBeNull()
        ->and($user->fresh()->status)->toBe(AccountStatus::Active);
});

test('a user can sign in again after cancelling', function () {
    $user = User::factory()->create(['deletion_requested_at' => now()]);

    $this->get(deletionCancelUrl($user));

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
});

test('the link can only be used once', function () {
    $user = User::factory()->create(['deletion_requested_at' => now()]);

    $url = deletionCancelUrl($user);

    $this->get($url);

    $this->get($url)
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'This cancellation link is invalid or has already been used.');
});

test('an old link cannot cancel a newer deletion request', function () {
    $user = User::factory()->create(['deletion_requested_at' => now()]);

    $oldUrl = deletionCancelUrl($user);

    $this->get($oldUrl);

    // The user asks for deletion again later.
    $this->travel(1)->minute();
    $user->forceFill(['deletion_requested_at' => now()])->save();

    $this->get($oldUrl)->assertRedirect(route('login'));

    expect($user->fresh()->deletion_requested_at)->not->toBeNull();
});

test('the newest link works for the newest request', function () {
    $user = User::factory()->create(['deletion_requested_at' => now()]);

    $this->get(deletionCancelUrl($user));

    $this->travel(1)->minute();
    $user->forceFill(['deletion_requested_at' => now()])->save();

    $this->get(deletionCancelUrl($user));

    expect($user->fresh()->deletion_requested_at)->toBeNull();
});

test('the link expires when the 30-day grace period ends', function () {
    $user = User::factory()->create(['deletion_requested_at' => now()]);

    $url = deletionCancelUrl($user);

    $this->travel(31)->days();

    $this->get($url)->assertForbidden();

    expect($user->fresh()->deletion_requested_at)->not->toBeNull();
});

test('the grace period is checked on the server even with a valid signature', function () {
    $user = User::factory()->create(['deletion_requested_at' => now()->subDays(31)]);

    $url = URL::temporarySignedRoute(
        'account-deletion.cancel',
        now()->addDay(),
        [
            'user' => $user->getRouteKey(),
            'requested' => $user->deletion_requested_at->getTimestamp(),
        ]
    );

    $this->get($url)
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'The account recovery period has expired.');

    expect($user->fresh()->deletion_requested_at)->not->toBeNull();
});

test('a tampered link is rejected', function () {
    $user = User::factory()->create(['deletion_requested_at' => now()]);

    $this->get(deletionCancelUrl($user).'x')->assertForbidden();

    expect($user->fresh()->deletion_requested_at)->not->toBeNull();
});

test('cancelling keeps a suspended account suspended', function () {
    $user = User::factory()->suspended()->create(['deletion_requested_at' => now()]);

    $this->get(deletionCancelUrl($user))->assertRedirect(route('login'));

    expect($user->fresh()->deletion_requested_at)->toBeNull()
        ->and($user->fresh()->status)->toBe(AccountStatus::Suspended);

    // Still blocked: cancelling must not bypass the Admin suspension.
    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('cancelling keeps a deactivated account deactivated', function () {
    $user = User::factory()->deactivated()->create(['deletion_requested_at' => now()]);

    $this->get(deletionCancelUrl($user))->assertRedirect(route('login'));

    expect($user->fresh()->deletion_requested_at)->toBeNull()
        ->and($user->fresh()->status)->toBe(AccountStatus::Deactivated);
});

test('an anonymized account cannot cancel a deletion request', function () {
    $user = User::factory()->deactivated()->create([
        'deletion_requested_at' => now(),
        'anonymized_at' => now(),
    ]);

    $this->get(deletionCancelUrl($user))
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'This cancellation link is invalid or has already been used.');

    expect($user->fresh()->deletion_requested_at)->not->toBeNull();
});

test('a user with no pending request gets the invalid link message', function () {
    $user = User::factory()->create();

    $url = URL::temporarySignedRoute(
        'account-deletion.cancel',
        now()->addDays(30),
        ['user' => $user->getRouteKey(), 'requested' => now()->getTimestamp()]
    );

    $this->get($url)
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'This cancellation link is invalid or has already been used.');
});
