<?php

use App\Notifications\CompanyRegistrationApproved;
use App\Notifications\CompanyRegistrationRejected;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Shagalni\Shared\Enums\RegistrationStatus;
use Shagalni\Shared\Enums\UserRole;
use Shagalni\Shared\Models\Company;
use Shagalni\Shared\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

function companyWithOwner(array $ownerState = [], array $companyState = []): Company
{
    $owner = User::factory()->create(array_merge(['role' => UserRole::CompanyOwner], $ownerState));

    return createCompanyRecord(array_merge([
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

test('approval email and in-app notification are queued with retries', function () {
    Queue::fake();

    $company = companyWithOwner();

    $this->actingAs(adminUser())
        ->post(route('companies.approve', $company))
        ->assertRedirect(route('companies.show', $company));

    $jobs = Queue::pushed(
        SendQueuedNotifications::class,
        fn (SendQueuedNotifications $job): bool => $job->notification instanceof CompanyRegistrationApproved
    );

    expect($jobs)->toHaveCount(2)
        ->and($jobs->pluck('channels')->all())->toContain(['database'], ['mail'])
        ->and($jobs->every(fn (SendQueuedNotifications $job): bool => $job->tries === 3))->toBeTrue();
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

test('rejection stores the decision reason in the owners in-app notification', function () {
    Config::set('services.job_seeker_portal_url', 'https://portal.example.test/company/register');

    $admin = adminUser();
    $company = companyWithOwner();
    $reason = 'The company description is missing.';

    $this->actingAs($admin)
        ->post(route('companies.reject', $company), [
            'rejection_reason' => $reason,
        ])
        ->assertRedirect(route('companies.show', $company));

    expect($company->owner->notifications)->toHaveCount(1)
        ->and($company->owner->notifications->first()->data['rejection_reason'])->toBe($reason)
        ->and($admin->notifications)->toHaveCount(0);
});

test('rejection queues email and in-app notifications with the decision-time reason', function () {
    Queue::fake();
    Config::set('services.job_seeker_portal_url', 'https://portal.example.test/company/register');

    $company = companyWithOwner();
    $reason = 'The company description is missing.';

    $this->actingAs(adminUser())
        ->post(route('companies.reject', $company), [
            'rejection_reason' => $reason,
        ])
        ->assertRedirect(route('companies.show', $company));

    $jobs = Queue::pushed(
        SendQueuedNotifications::class,
        fn (SendQueuedNotifications $job): bool => $job->notification instanceof CompanyRegistrationRejected
    );

    $mailJob = $jobs->first(
        fn (SendQueuedNotifications $job): bool => $job->channels === ['mail']
    );
    $databaseJob = $jobs->first(
        fn (SendQueuedNotifications $job): bool => $job->channels === ['database']
    );

    expect($jobs)->toHaveCount(2)
        ->and($mailJob)->not->toBeNull()
        ->and($databaseJob)->not->toBeNull()
        ->and($mailJob->tries)->toBe(3)
        ->and($databaseJob->tries)->toBe(3);

    $company->forceFill(['rejection_reason' => 'Changed after the decision.'])->save();

    $mail = $mailJob->notification->toMail($company->owner);
    $databasePayload = $databaseJob->notification->toArray($company->owner);

    expect($mail->introLines)->toContain('Reason: '.$reason)
        ->and($databasePayload['rejection_reason'])->toBe($reason);
});

test('rejection email directs the owner to the configured Job Seeker Portal', function () {
    Config::set('services.job_seeker_portal_url', 'https://portal.example.test/company/register');

    $company = companyWithOwner([], [
        'registration_status' => RegistrationStatus::Rejected,
        'rejection_reason' => 'The company description is missing.',
    ]);

    $mail = (new CompanyRegistrationRejected(
        $company,
        'The company description is missing.'
    ))->toMail($company->owner);

    expect($mail->introLines)
        ->toContain('You can edit your company details and resubmit your registration through the Job Seeker Portal.')
        ->and($mail->actionText)->toBe('Open Job Seeker Portal')
        ->and($mail->actionUrl)->toBe('https://portal.example.test/company/register')
        ->and(implode(' ', $mail->introLines))->not->toContain('Backoffice');
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
