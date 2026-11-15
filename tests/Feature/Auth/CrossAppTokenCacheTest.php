<?php

use App\Services\AccountLifecycleService;
use App\Services\CompanyOwnerInvitationService;
use Shagalni\Shared\Enums\AccountStatus;
use Shagalni\Shared\Enums\UserRole;
use Shagalni\Shared\Models\User;

test('reactivation and invitation tokens remain usable across app names with a fixed cache prefix', function () {
    config([
        'app.name' => 'Shagalni Backoffice',
        'cache.prefix' => 'shagalni-cache-',
    ]);

    $reactivationUser = User::factory()->deactivated()->create();
    $lifecycle = app(AccountLifecycleService::class);
    $reactivationToken = $lifecycle->issueReactivationToken($reactivationUser);

    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $invitations = app(CompanyOwnerInvitationService::class);
    $owner = $invitations->invite($admin, [
        'full_name' => 'Mona Ali',
        'email' => 'mona@example.com',
        'phone' => '01012345678',
        'company_name' => 'Nile Tech',
        'industry' => 'Software',
        'location' => 'Cairo',
    ]);
    $invitationToken = $invitations->issueToken($owner);

    config(['app.name' => 'Shagalni Job Seeker Portal']);

    expect(config('cache.prefix'))->toBe('shagalni-cache-')
        ->and($lifecycle->reactivateWithToken($reactivationUser, $reactivationToken)->status)
        ->toBe(AccountStatus::Active);

    $acceptedOwner = $invitations->accept($owner, $invitationToken, 'A-strong-password!123');

    expect($acceptedOwner->email_verified_at)->not->toBeNull();
});
