<?php

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('allows an approved company owner to view their company profile', function () {
    $owner = approvedOwner();

    $this->actingAs($owner)
        ->get(route('my-company.show'))
        ->assertOk()
        ->assertSee('Approved Co')
        ->assertSee('Software');
});

it('allows an approved owner to update directly editable company fields', function () {
    $owner = approvedOwner();

    $this->actingAs($owner)
        ->patch(route('my-company.update'), [
            'description' => 'A software development company.',
            'location' => 'Alexandria',
            'website' => 'https://example.com',
        ])
        ->assertRedirect(route('my-company.show'));

    $company = $owner->company()->first();

    expect($company->description)->toBe('A software development company.')
        ->and($company->location)->toBe('Alexandria')
        ->and($company->website)->toBe('https://example.com');

    $this->assertDatabaseCount('company_change_requests', 0);
});

it('does not allow company owners to update name or industry directly', function () {
    $owner = approvedOwner();

    $this->actingAs($owner)
        ->patch(route('my-company.update'), [
            'description' => 'Updated description',
            'location' => 'Cairo',
            'website' => 'https://example.com',
            'name' => 'Changed Name',
            'industry' => 'Finance',
        ])
        ->assertSessionHasErrors(['name', 'industry']);

    expect($owner->company()->first()->name)->toBe('Approved Co')
        ->and($owner->company()->first()->industry)->toBe('Software');
});

it('does not allow an admin to update a company through owner profile routes', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $this->actingAs($admin)
        ->get(route('my-company.show'))
        ->assertForbidden();
});

it('allows an owner to replace their company logo', function () {
    Storage::fake('public');

    $owner = approvedOwner();
    $company = $owner->company()->first();

    $oldLogo = 'company-logos/old-logo.png';

    Storage::disk('public')->put($oldLogo, 'old-logo-content');

    $company->update(['logo' => $oldLogo, ]);
   
        

    $this->actingAs($owner)
        ->patch(route('my-company.update'), [
            'description' => $company->description,
            'location' => $company->location,
            'website' => $company->website,
            'logo' => UploadedFile::fake()->image('new-logo.png'),
        ])
        ->assertRedirect(route('my-company.show'));

    $company->refresh();

    expect($company->logo)->not->toBe($oldLogo);

   
});
