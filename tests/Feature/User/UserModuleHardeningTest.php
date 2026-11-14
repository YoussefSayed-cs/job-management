<?php

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Livewire\Settings\Profile;
use App\Models\Application;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\QueryException;
use Livewire\Livewire;

beforeEach(function () {
    $this->withoutVite();
});

/**
 * Every Backoffice endpoint as [method, url].
 * Defined as a function because route() needs the app to be booted.
 */
function backofficeEndpoints(): array
{
    $user = User::factory()->create();
    $company = approvedOwner()->company;

    return [
        ['get', route('dashboard')],
        ['get', route('users.index')],
        ['get', route('users.show', $user)],
        ['post', route('users.suspend', $user)],
        ['post', route('users.deactivate', $user)],
        ['post', route('users.reactivate', $user)],
        ['get', route('company-owners.create')],
        ['post', route('company-owners.store')],
        ['post', route('users.resend-invitation', $user)],
        ['get', route('companies.index')],
        ['get', route('companies.show', $company)],
        ['post', route('companies.approve', $company)],
        ['post', route('companies.reject', $company)],
    ];
}

// ---------------------------------------------------------------------------
// Authentication hardening (NFR-SEC-05)
// ---------------------------------------------------------------------------

test('login is locked after five failed attempts', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
    }

    // Even the correct password is refused while the lockout is active.
    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertStatus(429);

    $this->assertGuest();
});

test('an anonymized account cannot sign in even if it is still marked active', function () {
    $user = User::factory()->create(['anonymized_at' => now()]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('a signed-in user is logged out on the next request after requesting deletion', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $user->forceFill(['deletion_requested_at' => now()])->save();

    $this->get('/settings/profile')->assertRedirect(route('login'));

    $this->assertGuest();
});

// ---------------------------------------------------------------------------
// Protected fields (FR-18, LLD 4.1)
// ---------------------------------------------------------------------------

test('protected user fields cannot be mass assigned', function (string $field) {
    expect((new User)->isFillable($field))->toBeFalse();
})->with([
    'role',
    'status',
    'email_verified_at',
    'deletion_requested_at',
    'anonymized_at',
]);

test('the profile form rejects an email that belongs to another user', function () {
    $other = User::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('email', $other->email)
        ->call('updateProfileInformation')
        ->assertHasErrors(['email' => 'unique']);

    expect($user->refresh()->email)->not->toBe($other->email);
});

test('the profile form rejects a phone number that belongs to another user', function () {
    $other = User::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('phone', $other->phone)
        ->call('updateProfileInformation')
        ->assertHasErrors(['phone' => 'unique']);

    expect($user->refresh()->phone)->not->toBe($other->phone);
});

// ---------------------------------------------------------------------------
// Database integrity (ODR-02)
// ---------------------------------------------------------------------------

test('a user who has applications cannot be physically deleted', function () {
    $user = User::factory()->create();

    Application::create([
        'user_id' => $user->id,
        'status' => 'pending',
        'job_title' => 'Laravel Developer',
        'company_name' => 'Acme',
        'submitted_at' => now(),
    ]);

    expect(fn () => $user->delete())->toThrow(QueryException::class);

    expect(User::find($user->id))->not->toBeNull();
});

// ---------------------------------------------------------------------------
// Backoffice access boundaries (FR-05, NFR-SEC-02)
// ---------------------------------------------------------------------------

test('a company owner with a rejected registration cannot enter the backoffice', function () {
    $owner = User::factory()->create(['role' => UserRole::CompanyOwner]);

    Company::create([
        'owner_id' => $owner->id,
        'name' => 'Rejected Co',
        'industry' => 'Software',
        'location' => 'Cairo',
        'registration_status' => 'rejected',
    ]);

    $this->actingAs($owner)->get(route('dashboard'))->assertForbidden();
});

test('guests are redirected to login from every backoffice route', function () {
    foreach (backofficeEndpoints() as [$method, $url]) {
        $this->{$method}($url)->assertRedirect(route('login'));
    }
});

test('a job seeker is forbidden from every backoffice route', function () {
    $seeker = User::factory()->create();

    foreach (backofficeEndpoints() as [$method, $url]) {
        $this->actingAs($seeker)->{$method}($url)->assertForbidden();
    }
});

test('a suspended admin-managed owner loses backoffice access immediately', function () {
    $owner = approvedOwner();

    $this->actingAs($owner);

    $owner->forceFill(['status' => AccountStatus::Suspended])->save();

    $this->get(route('dashboard'))->assertRedirect(route('login'));

    $this->assertGuest();
});
