<?php

use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use App\Notifications\CompanyRegistrationApproved;
use App\Notifications\CompanyRegistrationRejected;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->withoutVite();
});

function companyWithOwner(array $ownerState = [], array $companyState = []): Company
{
    $owner = User::factory()->create(array_merge(['role' => UserRole::CompanyOwner], $ownerState));

    return Company::create(array_merge([
        'owner_id' => $owner->id,
        'name' => 'Acme Software',
        'industry' => 'Software',
        'location' => 'Cairo',
        'registration_status' => RegistrationStatus::PendingApproval,
    ], $companyState));
}

test('admin can approve a pending company registration', function () {
    Notification::fake();

    $admin = adminUser();
    $company = companyWithOwner();

    $this->actingAs($admin)
        ->post(route('companies.approve', $company))
        ->assertRedirect(route('companies.show', $company));

    $company->refresh();

    expect($company->registration_status)->toBe(RegistrationStatus::Approved)
        ->and($company->reviewed_by)->toBe($admin->id)
        ->and($company->reviewed_at)->not->toBeNull()
        ->and($company->rejection_reason)->toBeNull();

    Notification::assertSentTo($company->owner, CompanyRegistrationApproved::class);
});

test('approval stores an in-app notification for the owner only', function () {
    $admin = adminUser();
    $company = companyWithOwner();

    $this->actingAs($admin)->post(route('companies.approve', $company));

    expect($company->owner->notifications)->toHaveCount(1)
        ->and($company->owner->notifications->first()->data['type'])->toBe('company_registration_approved')
        ->and($admin->notifications)->toHaveCount(0);
});

test('rejection requires a reason', function () {
    Notification::fake();

    $company = companyWithOwner();

    $this->actingAs(adminUser())
        ->post(route('companies.reject', $company), ['rejection_reason' => ''])
        ->assertSessionHasErrors('rejection_reason');

    expect($company->refresh()->registration_status)->toBe(RegistrationStatus::PendingApproval);

    Notification::assertNothingSent();
});

test('admin can reject a company registration with a reason', function () {
    Notification::fake();

    $admin = adminUser();
    $company = companyWithOwner();

    $this->actingAs($admin)
        ->post(route('companies.reject', $company), [
            'rejection_reason' => 'The company description is missing.',
        ])
        ->assertRedirect(route('companies.show', $company));

    $company->refresh();

    expect($company->registration_status)->toBe(RegistrationStatus::Rejected)
        ->and($company->rejection_reason)->toBe('The company description is missing.')
        ->and($company->reviewed_by)->toBe($admin->id);

    Notification::assertSentTo($company->owner, CompanyRegistrationRejected::class);
});

test('a registration cannot be reviewed before the owner verifies their email', function () {
    Notification::fake();

    $company = companyWithOwner(['email_verified_at' => null]);

    $this->actingAs(adminUser())
        ->post(route('companies.approve', $company))
        ->assertSessionHasErrors('company');

    $this->actingAs(adminUser())
        ->post(route('companies.reject', $company), [
            'rejection_reason' => 'The company description is missing.',
        ])
        ->assertSessionHasErrors('company');

    expect($company->refresh()->registration_status)->toBe(RegistrationStatus::PendingApproval);

    Notification::assertNothingSent();
});

test('an already reviewed registration cannot be reviewed again', function () {
    Notification::fake();

    $company = companyWithOwner([], ['registration_status' => RegistrationStatus::Approved]);

    $this->actingAs(adminUser())
        ->post(route('companies.approve', $company))
        ->assertSessionHasErrors('company');

    Notification::assertNothingSent();
});

test('a company owner cannot access the company review pages', function () {
    $company = companyWithOwner([], ['registration_status' => RegistrationStatus::Approved]);

    $this->actingAs($company->owner)->get(route('companies.index'))->assertForbidden();
    $this->actingAs($company->owner)->get(route('companies.show', $company))->assertForbidden();
    $this->actingAs($company->owner)->post(route('companies.approve', $company))->assertForbidden();
    $this->actingAs($company->owner)->post(route('companies.reject', $company), [
        'rejection_reason' => 'Trying to reject myself.',
    ])->assertForbidden();
});

test('a company owner with a pending registration cannot enter the backoffice', function () {
    $company = companyWithOwner();

    $this->actingAs($company->owner)->get('/dashboard')->assertForbidden();
});

test('admin can list and filter companies', function () {
    companyWithOwner();
    companyWithOwner([], [
        'name' => 'Already Approved Co',
        'registration_status' => RegistrationStatus::Approved,
    ]);

    $this->actingAs(adminUser())
        ->get(route('companies.index', ['status' => 'pending_approval']))
        ->assertOk()
        ->assertSee('Acme Software')
        ->assertDontSee('Already Approved Co');
});

test('admin can view a company details page', function () {
    $company = companyWithOwner();

    $this->actingAs(adminUser())
        ->get(route('companies.show', $company))
        ->assertOk()
        ->assertSee('Acme Software')
        ->assertSee($company->owner->email);
});
