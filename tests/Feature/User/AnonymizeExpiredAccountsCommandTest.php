<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Services\AccountAnonymizationService;
use Mockery\MockInterface;

// unique name on purpose: Pest test files share one global function namespace
function expiredDeletionUser(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'role' => UserRole::JobSeeker,
        'deletion_requested_at' => now()->subDays(31),
    ], $attributes));
}

test('it anonymizes accounts whose grace period has ended', function () {
    $user = expiredDeletionUser();

    $this->artisan('accounts:anonymize-expired')->assertSuccessful();

    expect($user->fresh()->anonymized_at)->not->toBeNull();
});

test('it does not touch accounts still inside the grace period', function () {
    $user = expiredDeletionUser(['deletion_requested_at' => now()->subDays(29)]);

    $this->artisan('accounts:anonymize-expired')->assertSuccessful();

    expect($user->fresh()->anonymized_at)->toBeNull();
});

test('it ignores accounts without a deletion request', function () {
    $user = expiredDeletionUser(['deletion_requested_at' => null]);

    $this->artisan('accounts:anonymize-expired')
        ->expectsOutputToContain('No accounts are due')
        ->assertSuccessful();

    expect($user->fresh()->anonymized_at)->toBeNull();
});

test('it never anonymizes the admin account', function () {
    $admin = expiredDeletionUser(['role' => UserRole::Admin]);

    $this->artisan('accounts:anonymize-expired')->assertSuccessful();

    expect($admin->fresh()->anonymized_at)->toBeNull();
});

test('it skips accounts that are already anonymized', function () {
    $anonymizedAt = now()->subDays(5)->startOfSecond();
    $user = expiredDeletionUser(['anonymized_at' => $anonymizedAt]);

    $this->artisan('accounts:anonymize-expired')->assertSuccessful();

    expect($user->fresh()->anonymized_at->equalTo($anonymizedAt))->toBeTrue();
});

test('dry run lists due accounts without changing them', function () {
    $user = expiredDeletionUser();

    $this->artisan('accounts:anonymize-expired --dry-run')
        ->expectsOutputToContain($user->id)
        ->assertSuccessful();

    expect($user->fresh()->anonymized_at)->toBeNull();
});

test('a failure on one account does not stop the others', function () {
    $bad = expiredDeletionUser();
    $good = expiredDeletionUser();

    $this->mock(AccountAnonymizationService::class, function (MockInterface $mock) use ($bad, $good) {
        $mock->shouldReceive('anonymize')
            ->once()
            ->withArgs(fn (User $user) => $user->is($bad))
            ->andThrow(new RuntimeException('boom'));

        $mock->shouldReceive('anonymize')
            ->once()
            ->withArgs(fn (User $user) => $user->is($good));
    });

    $this->artisan('accounts:anonymize-expired')->assertFailed();
});

test('the command is scheduled', function () {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('accounts:anonymize-expired')
        ->assertSuccessful();
});
