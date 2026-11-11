<?php

use App\Enums\UserRole;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

test('guests are redirected to login', function () {
    $this->get(route('users.index'))->assertRedirect(route('login'));
});

test('job seekers and company owners cannot access user management', function () {
    $target = User::factory()->create();

    foreach ([User::factory()->create(), approvedOwner()] as $actor) {
        $this->actingAs($actor)->get(route('users.index'))->assertForbidden();
        $this->actingAs($actor)->get(route('users.show', $target))->assertForbidden();
    }
});

test('admin lists only company owners and job seekers', function () {
    User::factory()->create(['full_name' => 'Sara Seeker']);
    User::factory()->create(['full_name' => 'Hidden Admin', 'role' => UserRole::Admin]);

    $this->actingAs(adminUser())
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee('Sara Seeker')
        ->assertDontSee('Hidden Admin');
});

test('anonymized users are not listed', function () {
    User::factory()->deactivated()->create([
        'full_name' => 'Gone Person',
        'anonymized_at' => now(),
    ]);

    $this->actingAs(adminUser())
        ->get(route('users.index'))
        ->assertDontSee('Gone Person');
});

test('admin can filter users by role, status and search', function () {
    User::factory()->create(['full_name' => 'Active Seeker']);
    User::factory()->suspended()->create(['full_name' => 'Suspended Seeker']);
    User::factory()->create(['full_name' => 'Owner Person', 'role' => UserRole::CompanyOwner]);

    $admin = adminUser();

    $this->actingAs($admin)->get(route('users.index', ['status' => 'suspended']))
        ->assertSee('Suspended Seeker')->assertDontSee('Active Seeker');

    $this->actingAs($admin)->get(route('users.index', ['role' => 'company_owner']))
        ->assertSee('Owner Person')->assertDontSee('Active Seeker');

    $this->actingAs($admin)->get(route('users.index', ['search' => 'Owner']))
        ->assertSee('Owner Person')->assertDontSee('Active Seeker');
});

test('invalid filters are rejected', function () {
    $this->actingAs(adminUser())
        ->get(route('users.index', ['role' => 'admin']))
        ->assertSessionHasErrors('role');
});

test('admin can view a job seeker details page', function () {
    $user = User::factory()->create();

    $this->actingAs(adminUser())
        ->get(route('users.show', $user))
        ->assertOk()
        ->assertSee($user->email);
});

test('admin cannot open another admin account', function () {
    $this->actingAs(adminUser())
        ->get(route('users.show', adminUser()))
        ->assertForbidden();
});