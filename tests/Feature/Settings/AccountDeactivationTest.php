<?php

use App\Enums\AccountStatus;
use App\Livewire\Settings\DeactivateAccountForm;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->withoutVite();
});

test('user can deactivate their own account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(DeactivateAccountForm::class)
        ->set('password', 'password')
        ->call('deactivate')
        ->assertHasNoErrors();

    expect($user->fresh()->status)->toBe(AccountStatus::Deactivated);
    $this->assertGuest();
});

test('company owner can deactivate their own account', function () {
    $owner = approvedOwner();

    $this->actingAs($owner);

    Livewire::test(DeactivateAccountForm::class)
        ->set('password', 'password')
        ->call('deactivate')
        ->assertHasNoErrors();

    expect($owner->fresh()->status)->toBe(AccountStatus::Deactivated);
});

test('correct password must be provided to deactivate the account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(DeactivateAccountForm::class)
        ->set('password', 'wrong-password')
        ->call('deactivate')
        ->assertHasErrors(['password']);

    expect($user->fresh()->status)->toBe(AccountStatus::Active);
});

test('the admin account cannot be deactivated', function () {
    $admin = adminUser();

    $this->actingAs($admin);

    Livewire::test(DeactivateAccountForm::class)
        ->set('password', 'password')
        ->call('deactivate')
        ->assertHasErrors(['password']);

    expect($admin->fresh()->status)->toBe(AccountStatus::Active);
});
