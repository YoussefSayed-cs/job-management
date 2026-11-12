<?php

use App\Enums\AccountStatus;
use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use App\Notifications\CompanyOwnerInvitation;
use App\Services\CompanyOwnerInvitationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    $this->withoutVite();
});

function invitationPayload(array $overrides = []): array
{
    return array_merge([
        'full_name' => 'Mona Ali',
        'email' => 'mona@example.com',
        'phone' => '01012345678',
        'company_name' => 'Nile Tech',
        'industry' => 'Software',
        'location' => 'Cairo',
        'description' => 'We build things.',
        'website' => 'https://niletech.example.com',
    ], $overrides);
}

/** @return array{0: User, 1: string} */
function pendingInvitedOwner(): array
{
    $service = app(CompanyOwnerInvitationService::class);

    $owner = $service->invite(adminUser(), invitationPayload());

    return [$owner, $service->issueToken($owner)];
}

function invitationUrl(User $owner, string $token, string $route = 'company-owner-invitation.show'): string
{
    return URL::temporarySignedRoute($route, now()->addHours(48), [
        'user' => $owner->getRouteKey(),
        'token' => $token,
    ]);
}

function strongPasswordPayload(): array
{
    return [
        'password' => 'Str0ng-Password!123',
        'password_confirmation' => 'Str0ng-Password!123',
    ];
}

test('admin creates a company owner with an approved company and an invitation is sent', function () {
    Notification::fake();

    $admin = adminUser();

    $this->actingAs($admin)
        ->post(route('company-owners.store'), invitationPayload())
        ->assertRedirect();

    $owner = User::where('email', 'mona@example.com')->firstOrFail();

    expect($owner->role)->toBe(UserRole::CompanyOwner)
        ->and($owner->status)->toBe(AccountStatus::Active)
        ->and($owner->email_verified_at)->toBeNull()
        ->and($owner->company->name)->toBe('Nile Tech')
        ->and($owner->company->registration_status)->toBe(RegistrationStatus::Approved)
        ->and($owner->company->reviewed_by)->toBe($admin->id);

    Notification::assertSentTo($owner, CompanyOwnerInvitation::class);
});

test('only an admin can create company owners', function () {
    $this->actingAs(approvedOwner())->get(route('company-owners.create'))->assertForbidden();

    $this->actingAs(approvedOwner())
        ->post(route('company-owners.store'), invitationPayload())
        ->assertForbidden();

    expect(User::where('email', 'mona@example.com')->exists())->toBeFalse();
});

test('creating a company owner validates the input', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs(adminUser())
        ->post(route('company-owners.store'), invitationPayload([
            'email' => 'taken@example.com',
            'company_name' => '',
        ]))
        ->assertSessionHasErrors(['email', 'company_name']);

    expect(Company::count())->toBe(0);
});

test('the account is kept when the invitation email fails', function () {
    $this->partialMock(CompanyOwnerInvitationService::class, function ($mock) {
        $mock->shouldReceive('sendInvitation')->once()->andReturn(false);
    });

    $this->actingAs(adminUser())
        ->post(route('company-owners.store'), invitationPayload())
        ->assertSessionHasErrors('account');

    expect(User::where('email', 'mona@example.com')->exists())->toBeTrue();
});

test('an invited owner cannot sign in before setting a password', function () {
    [$owner] = pendingInvitedOwner();

    $this->post(route('login.store'), ['email' => $owner->email, 'password' => 'password']);

    $this->assertGuest();
});

test('the invitation mail contains a signed link with the token', function () {
    [$owner] = pendingInvitedOwner();

    $mail = (new CompanyOwnerInvitation('abc123'))->toMail($owner);

    expect($mail->actionUrl)->toContain('company-owner-invitation')
        ->toContain('signature=')
        ->toContain('token=abc123');
});

test('a valid link shows the set password page', function () {
    [$owner, $token] = pendingInvitedOwner();

    $this->get(invitationUrl($owner, $token))
        ->assertOk()
        ->assertSee($owner->email);
});

test('accepting the invitation sets the password, verifies the email and gives backoffice access', function () {
    [$owner, $token] = pendingInvitedOwner();

    $this->post(invitationUrl($owner, $token, 'company-owner-invitation.store'), strongPasswordPayload())
        ->assertRedirect(route('login'));

    $owner->refresh();

    expect(Hash::check('Str0ng-Password!123', $owner->password))->toBeTrue()
        ->and($owner->hasVerifiedEmail())->toBeTrue();

    $this->actingAs($owner)->get(route('dashboard'))->assertOk();
});

test('the invitation link works only once', function () {
    [$owner, $token] = pendingInvitedOwner();
    $url = invitationUrl($owner, $token, 'company-owner-invitation.store');

    $this->post($url, strongPasswordPayload())->assertRedirect(route('login'));

    $this->post($url, [
        'password' => 'Another-Pass-456!x',
        'password_confirmation' => 'Another-Pass-456!x',
    ])->assertRedirect(route('login'));

    expect(Hash::check('Str0ng-Password!123', $owner->refresh()->password))->toBeTrue();

    $this->get(invitationUrl($owner, $token))->assertRedirect(route('login'));
});

test('a weak password is rejected and the invitation stays usable', function () {
    [$owner, $token] = pendingInvitedOwner();

    $this->post(invitationUrl($owner, $token, 'company-owner-invitation.store'), [
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertSessionHasErrors('password');

    expect($owner->refresh()->hasVerifiedEmail())->toBeFalse();

    $this->get(invitationUrl($owner, $token))->assertOk();
});

test('a wrong token is refused', function () {
    [$owner] = pendingInvitedOwner();

    $this->get(invitationUrl($owner, 'wrong-token'))->assertRedirect(route('login'));
});

test('the link expires after 48 hours', function () {
    [$owner, $token] = pendingInvitedOwner();
    $url = invitationUrl($owner, $token);

    $this->travel(49)->hours();

    $this->get($url)->assertForbidden();
});

test('admin can resend the invitation and the old link stops working', function () {
    Notification::fake();

    [$owner, $oldToken] = pendingInvitedOwner();

    $this->actingAs(adminUser())
        ->post(route('users.resend-invitation', $owner))
        ->assertRedirect(route('users.show', $owner));

    Notification::assertSentTo($owner, CompanyOwnerInvitation::class);

    expect(app(CompanyOwnerInvitationService::class)->hasValidToken($owner, $oldToken))->toBeFalse();
});

test('an invitation cannot be resent after it was accepted or to other users', function () {
    [$owner] = pendingInvitedOwner();
    $owner->forceFill(['email_verified_at' => now()])->save();

    $admin = adminUser();

    $this->actingAs($admin)
        ->post(route('users.resend-invitation', $owner))
        ->assertForbidden();

    $this->actingAs($admin)
        ->post(route('users.resend-invitation', User::factory()->create()))
        ->assertForbidden();
});