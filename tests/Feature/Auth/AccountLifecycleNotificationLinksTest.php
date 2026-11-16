<?php

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Shagalni\Shared\Models\User;
use Shagalni\Shared\Notifications\AccountDeletionRequested;
use Shagalni\Shared\Notifications\AccountReactivationRequested;

test('account lifecycle notifications send signed links to their named routes', function () {
    Notification::fake();

    $deletionUser = User::factory()->create(['deletion_requested_at' => now()]);
    $reactivationUser = User::factory()->deactivated()->create();

    expect(new AccountDeletionRequested)
        ->toBeInstanceOf(ShouldQueue::class)
        ->and((new AccountDeletionRequested)->tries)->toBe(3)
        ->and(new AccountReactivationRequested('single-use-token'))
        ->toBeInstanceOf(ShouldQueue::class)
        ->and((new AccountReactivationRequested('single-use-token'))->tries)->toBe(3);

    $deletionUser->notify(new AccountDeletionRequested);
    $reactivationUser->notify(new AccountReactivationRequested('single-use-token'));

    $deletionUrl = null;
    Notification::assertSentTo($deletionUser, AccountDeletionRequested::class, function ($notification) use ($deletionUser, &$deletionUrl) {
        $deletionUrl = $notification->toMail($deletionUser)->actionUrl;

        return true;
    });

    $reactivationUrl = null;
    Notification::assertSentTo($reactivationUser, AccountReactivationRequested::class, function ($notification) use ($reactivationUser, &$reactivationUrl) {
        $reactivationUrl = $notification->toMail($reactivationUser)->actionUrl;

        return true;
    });

    expect($deletionUrl)->not->toBeNull()
        ->and($reactivationUrl)->not->toBeNull();

    $deletionUrl = (string) $deletionUrl;
    $reactivationUrl = (string) $reactivationUrl;

    expect(parse_url($deletionUrl, PHP_URL_PATH))
        ->toBe(parse_url(route('account-deletion.cancel', [
            'user' => $deletionUser->getRouteKey(),
            'requested' => $deletionUser->deletion_requested_at->getTimestamp(),
        ]), PHP_URL_PATH))
        ->and(URL::hasValidSignature(Request::create($deletionUrl)))
        ->toBeTrue()
        ->and(parse_url($reactivationUrl, PHP_URL_PATH))
        ->toBe(parse_url(route('account-reactivation.confirm', [
            'user' => $reactivationUser->getRouteKey(),
            'token' => 'single-use-token',
        ]), PHP_URL_PATH))
        ->and(URL::hasValidSignature(Request::create($reactivationUrl)))
        ->toBeTrue();
});
