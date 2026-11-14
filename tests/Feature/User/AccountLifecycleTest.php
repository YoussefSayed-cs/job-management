<?php

use App\Enums\AccountStatus;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

test('guests are redirected to login', function () {
    $target = User::factory()->create();

    $this->post(route('users.suspend', $target))->assertRedirect(route('login'));
    $this->post(route('users.deactivate', $target))->assertRedirect(route('login'));
    $this->post(route('users.reactivate', $target))->assertRedirect(route('login'));
});

test('job seekers and company owners cannot change account status', function () {
    $target = User::factory()->create();

    foreach ([User::factory()->create(), approvedOwner()] as $actor) {
        $this->actingAs($actor)->post(route('users.suspend', $target))->assertForbidden();
        $this->actingAs($actor)->post(route('users.deactivate', $target))->assertForbidden();
        $this->actingAs($actor)->post(route('users.reactivate', $target))->assertForbidden();
    }

    expect($target->fresh()->status)->toBe(AccountStatus::Active);
});

test('admin can suspend an active user', function () {
    $user = User::factory()->create();

    $this->actingAs(adminUser())
        ->post(route('users.suspend', $user))
        ->assertRedirect(route('users.show', $user));

    expect($user->fresh()->status)->toBe(AccountStatus::Suspended);
});

test('admin can suspend a deactivated user', function () {
    $user = User::factory()->deactivated()->create();

    $this->actingAs(adminUser())->post(route('users.suspend', $user));

    expect($user->fresh()->status)->toBe(AccountStatus::Suspended);
});

test('admin cannot suspend an account that is already suspended', function () {
    $user = User::factory()->suspended()->create();

    $this->actingAs(adminUser())
        ->post(route('users.suspend', $user))
        ->assertSessionHasErrors('account');
});

test('admin can deactivate an active user', function () {
    $user = User::factory()->create();

    $this->actingAs(adminUser())->post(route('users.deactivate', $user));

    expect($user->fresh()->status)->toBe(AccountStatus::Deactivated);
});

test('admin cannot deactivate a suspended user', function () {
    $user = User::factory()->suspended()->create();

    $this->actingAs(adminUser())
        ->post(route('users.deactivate', $user))
        ->assertSessionHasErrors('account');

    expect($user->fresh()->status)->toBe(AccountStatus::Suspended);
});

test('admin can reactivate suspended and deactivated users', function () {
    $admin = adminUser();

    $suspended = User::factory()->suspended()->create();
    $deactivated = User::factory()->deactivated()->create();

    $this->actingAs($admin)->post(route('users.reactivate', $suspended));
    $this->actingAs($admin)->post(route('users.reactivate', $deactivated));

    expect($suspended->fresh()->status)->toBe(AccountStatus::Active)
        ->and($deactivated->fresh()->status)->toBe(AccountStatus::Active);
});

test('admin cannot reactivate an active user', function () {
    $user = User::factory()->create();

    $this->actingAs(adminUser())
        ->post(route('users.reactivate', $user))
        ->assertSessionHasErrors('account');
});

test('admin cannot manage another admin account', function () {
    $otherAdmin = adminUser();

    $this->actingAs(adminUser())->post(route('users.suspend', $otherAdmin))->assertForbidden();
    $this->actingAs(adminUser())->post(route('users.deactivate', $otherAdmin))->assertForbidden();
    $this->actingAs(adminUser())->post(route('users.reactivate', $otherAdmin))->assertForbidden();
});

test('admin cannot manage an anonymized account', function () {
    $tombstone = User::factory()->deactivated()->create(['anonymized_at' => now()]);

    $admin = adminUser();

    $this->actingAs($admin)->post(route('users.suspend', $tombstone))->assertForbidden();
    $this->actingAs($admin)->post(route('users.reactivate', $tombstone))->assertForbidden();

    expect($tombstone->fresh()->status)->toBe(AccountStatus::Deactivated);
});

test('suspended and deactivated users cannot log in', function () {
    foreach ([User::factory()->suspended()->create(), User::factory()->deactivated()->create()] as $user) {
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }
});

test('a signed-in user is logged out on the next request after suspension', function () {
    $user = User::factory()->suspended()->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('a suspended company owner cannot enter the backoffice', function () {
    $owner = approvedOwner();
    $owner->forceFill(['status' => AccountStatus::Suspended])->save();

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

test('user details page shows the right actions for each status', function () {
    $admin = adminUser();

    $active = User::factory()->create();
    $suspended = User::factory()->suspended()->create();

    $this->actingAs($admin)->get(route('users.show', $active))
        ->assertOk()
        ->assertSee(route('users.suspend', $active), false)
        ->assertSee(route('users.deactivate', $active), false)
        ->assertDontSee(route('users.reactivate', $active), false);

    $this->actingAs($admin)->get(route('users.show', $suspended))
        ->assertOk()
        ->assertSee(route('users.reactivate', $suspended), false)
        ->assertDontSee(route('users.deactivate', $suspended), false);
});
