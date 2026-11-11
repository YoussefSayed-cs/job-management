<?php

use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('admin can visit the dashboard', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->get(route('dashboard'))->assertOk();
});

test('company owner with an approved company can visit the dashboard', function () {
    $owner = User::factory()->create(['role' => UserRole::CompanyOwner]);

    Company::create([
        'owner_id' => $owner->id,
        'name' => 'Acme Software',
        'industry' => 'Software',
        'location' => 'Cairo',
        'registration_status' => RegistrationStatus::Approved,
    ]);

    $this->actingAs($owner)->get(route('dashboard'))->assertOk();
});

test('job seekers cannot visit the backoffice dashboard', function () {
    $jobSeeker = User::factory()->create(['role' => UserRole::JobSeeker]);

    $this->actingAs($jobSeeker)->get(route('dashboard'))->assertForbidden();
});