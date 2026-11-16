<?php

use App\Livewire\Settings\Profile;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use RuntimeException;
use Shagalni\Shared\Enums\UserRole;
use Shagalni\Shared\Models\User;
use Shagalni\Shared\Notifications\AccountDeletionRequested;

beforeEach(function () {
    $this->withoutVite();
});

test('profile page is displayed', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/settings/profile')->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('full_name', 'Test User')
        ->set('phone', '01099999999')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->full_name)->toBe('Test User')
        ->and($user->phone)->toBe('01099999999');
});

test('profile update allows a phone number shared with another account', function () {
    User::factory()->create(['phone' => '01011112222']);
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('phone', '01011112222')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect($user->fresh()->phone)->toBe('01011112222');
});

test('changing the email requires verification of the new address', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('email', 'new-address@example.com')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->email)->toBe('new-address@example.com')
        ->and($user->email_verified_at)->toBeNull();

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('full_name', 'Test User')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();

    Notification::assertNothingSent();
});

test('profile picture can be uploaded and replaced', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('profilePicture', UploadedFile::fake()->image('first.jpg'))
        ->call('updateProfilePicture')
        ->assertHasNoErrors();

    $first = $user->refresh()->profile_picture;

    expect($first)->not->toBeNull();
    Storage::disk('public')->assertExists($first);

    Livewire::test(Profile::class)
        ->set('profilePicture', UploadedFile::fake()->image('second.png'))
        ->call('updateProfilePicture')
        ->assertHasNoErrors();

    $second = $user->refresh()->profile_picture;

    expect($second)->not->toBe($first);
    Storage::disk('public')->assertExists($second);
    Storage::disk('public')->assertMissing($first);
});

test('profile picture rejects invalid files', function () {
    Storage::fake('public');

    $this->actingAs(User::factory()->create());

    Livewire::test(Profile::class)
        ->set('profilePicture', UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'))
        ->call('updateProfilePicture')
        ->assertHasErrors(['profilePicture']);

    Livewire::test(Profile::class)
        ->set('profilePicture', UploadedFile::fake()->image('huge.jpg')->size(3000))
        ->call('updateProfilePicture')
        ->assertHasErrors(['profilePicture']);
});

test('profile picture can be removed', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('profilePicture', UploadedFile::fake()->image('avatar.jpg'))
        ->call('updateProfilePicture');

    $path = $user->refresh()->profile_picture;

    Livewire::test(Profile::class)->call('removeProfilePicture');

    expect($user->refresh()->profile_picture)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('user can request account deletion', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $user->refresh();

    // الـ row بتفضل موجودة (30 يوم grace period) والـ status مبيتغيرش
    expect($user->deletion_requested_at)->not->toBeNull()
        ->and($user->status->value)->toBe('active');

    expect(auth()->check())->toBeFalse();

    Notification::assertSentTo($user, AccountDeletionRequested::class);
});

test('a failed deletion email remains queued for retries without losing the request', function () {
    Queue::fake();

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $queuedJob = Queue::pushed(SendQueuedNotifications::class)->first();

    expect($queuedJob)->not->toBeNull()
        ->and($queuedJob->notification)->toBeInstanceOf(AccountDeletionRequested::class)
        ->and($queuedJob->tries)->toBe(3);

    $channelManager = Mockery::mock(ChannelManager::class);
    $channelManager->shouldReceive('sendNow')
        ->once()
        ->andThrow(new RuntimeException('Mail server unavailable.'));

    expect(fn () => $queuedJob->handle($channelManager))
        ->toThrow(RuntimeException::class, 'Mail server unavailable.');

    expect($user->fresh()->deletion_requested_at)->not->toBeNull()
        ->and(auth()->check())->toBeFalse();
});

test('correct password must be provided to request account deletion', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('settings.delete-user-form')
        ->set('password', 'wrong-password')
        ->call('deleteUser')
        ->assertHasErrors(['password']);

    expect($user->refresh()->deletion_requested_at)->toBeNull();
});

test('the admin account cannot request account deletion', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin);

    Livewire::test('settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertHasErrors(['password']);

    expect($admin->refresh()->deletion_requested_at)->toBeNull();
});
