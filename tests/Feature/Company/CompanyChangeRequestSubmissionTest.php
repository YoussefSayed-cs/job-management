<?php

use App\Enums\UserRole;
use App\Models\CompanyChangeRequest;
use App\Models\User;

it('allows an approved company owner to submit a company name change request without changing approved data', function () {
    $owner = approvedOwner();
    $company = $owner->company;

    $this->actingAs($owner)
        ->post(route('my-company.change-requests.store'), [
            'new_name' => 'New Approved Co Name',
        ])
        ->assertRedirect(route('my-company.change-requests.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('company_change_requests', [
        'company_id' => $company->id,
        'new_name' => 'New Approved Co Name',
        'new_industry' => null,
        'status' => 'pending',
    ]);

    expect($company->fresh()->name)->toBe('Approved Co')
        ->and($company->fresh()->industry)->toBe('Software');
});

it('allows an approved company owner to request an industry change only', function () {
    $owner = approvedOwner();
    $company = $owner->company;

    $this->actingAs($owner)
        ->post(route('my-company.change-requests.store'), [
            'new_industry' => 'Financial Technology',
        ])
        ->assertRedirect(route('my-company.change-requests.index'));

    $this->assertDatabaseHas('company_change_requests', [
        'company_id' => $company->id,
        'new_name' => null,
        'new_industry' => 'Financial Technology',
        'status' => 'pending',
    ]);

    expect($company->fresh()->industry)->toBe('Software');
});

it('allows an owner to request name and industry changes together', function () {
    $owner = approvedOwner();
    $company = $owner->company;

    $this->actingAs($owner)
        ->post(route('my-company.change-requests.store'), [
            'new_name' => 'Next Company',
            'new_industry' => 'Artificial Intelligence',
        ])
        ->assertRedirect(route('my-company.change-requests.index'));

    $this->assertDatabaseHas('company_change_requests', [
        'company_id' => $company->id,
        'new_name' => 'Next Company',
        'new_industry' => 'Artificial Intelligence',
        'status' => 'pending',
    ]);
});

it('rejects a request when the proposed value is the same as the current value', function () {
    $owner = approvedOwner();

    $this->actingAs($owner)
        ->post(route('my-company.change-requests.store'), [
            'new_name' => ' approved co ',
        ])
        ->assertSessionHasErrors('new_name');

    $this->assertDatabaseCount('company_change_requests', 0);
});

it('does not allow more than one pending change request for a company', function () {
    $owner = approvedOwner();
    $company = $owner->company;

    CompanyChangeRequest::create([
        'company_id' => $company->id,
        'new_name' => 'First Request',
        'status' => 'pending',
    ]);

    $this->actingAs($owner)
        ->post(route('my-company.change-requests.store'), [
            'new_industry' => 'New Industry',
        ])
        ->assertSessionHasErrors('change_request');

    $this->assertDatabaseCount('company_change_requests', 1);
});

it('requires at least one company field to be submitted', function () {
    $owner = approvedOwner();

    $this->actingAs($owner)
        ->post(route('my-company.change-requests.store'), [])
        ->assertSessionHasErrors(['new_name', 'new_industry']);

    $this->assertDatabaseCount('company_change_requests', 0);
});

it('does not allow a job seeker to access company change requests', function () {
    $jobSeeker = User::factory()->create([
        'role' => UserRole::JobSeeker,
    ]);

    $this->actingAs($jobSeeker)
        ->get(route('my-company.change-requests.index'))
        ->assertForbidden();
});

it('shows the approved company owner their change request history', function () {
    $owner = approvedOwner();

    CompanyChangeRequest::create([
        'company_id' => $owner->company->id,
        'new_name' => 'Requested Company Name',
        'status' => 'pending',
    ]);

    $this->actingAs($owner)
        ->get(route('my-company.change-requests.index'))
        ->assertOk()
        ->assertSee('Company Change Requests')
        ->assertSee('Request History')
        ->assertSee('Requested Company Name')
        ->assertSee('Pending');
});
