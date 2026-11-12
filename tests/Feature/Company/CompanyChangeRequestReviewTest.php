<?php

use App\Enums\ChangeRequestStatus;
use App\Enums\UserRole;
use App\Models\CompanyChangeRequest;
use App\Models\User;

it('allows admin to view the change request queue', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->get(route('company-change-requests.index'))
        ->assertOk();
});

it('allows admin to approve a company name change', function () {
    $admin = adminUser();
    $owner = approvedOwner();
    $company = $owner->company;

    $changeRequest = CompanyChangeRequest::query()->create([
        'company_id' => $company->id,
        'new_name' => 'New Company Name',
        'new_industry' => null,
        'status' => ChangeRequestStatus::Pending,
    ]);

    $this->actingAs($admin)
        ->post(route('company-change-requests.approve', $changeRequest))
        ->assertRedirect();

    expect($company->fresh()->name)->toBe('New Company Name')
        ->and($changeRequest->fresh()->status)->toBe(ChangeRequestStatus::Approved);
});

it('allows admin to approve an industry change', function () {
    $admin = adminUser();
    $owner = approvedOwner();
    $company = $owner->company;

    $changeRequest = CompanyChangeRequest::query()->create([
        'company_id' => $company->id,
        'new_name' => null,
        'new_industry' => 'Technology',
        'status' => ChangeRequestStatus::Pending,
    ]);

    $this->actingAs($admin)
        ->post(route('company-change-requests.approve', $changeRequest))
        ->assertRedirect();

    expect($company->fresh()->industry)->toBe('Technology');
});

it('allows admin to approve both name and industry changes', function () {
    $admin = adminUser();
    $owner = approvedOwner();
    $company = $owner->company;

    $changeRequest = CompanyChangeRequest::query()->create([
        'company_id' => $company->id,
        'new_name' => 'Updated Company',
        'new_industry' => 'Technology',
        'status' => ChangeRequestStatus::Pending,
    ]);

    $this->actingAs($admin)
        ->post(route('company-change-requests.approve', $changeRequest))
        ->assertRedirect();

    expect($company->fresh()->name)->toBe('Updated Company')
        ->and($company->fresh()->industry)->toBe('Technology');
});

it('preserves unchanged company fields when approving a partial request', function () {
    $admin = adminUser();
    $owner = approvedOwner();
    $company = $owner->company;

    $originalIndustry = $company->industry;

    $changeRequest = CompanyChangeRequest::query()->create([
        'company_id' => $company->id,
        'new_name' => 'Updated Company',
        'new_industry' => null,
        'status' => ChangeRequestStatus::Pending,
    ]);

    $this->actingAs($admin)
        ->post(route('company-change-requests.approve', $changeRequest))
        ->assertRedirect();

    expect($company->fresh()->industry)->toBe($originalIndustry);
});

it('allows admin to reject a change request', function () {
    $admin = adminUser();
    $owner = approvedOwner();

    $changeRequest = CompanyChangeRequest::query()->create([
        'company_id' => $owner->company->id,
        'new_name' => 'Rejected Name',
        'status' => ChangeRequestStatus::Pending,
    ]);

    $this->actingAs($admin)
        ->post(route('company-change-requests.reject', $changeRequest))
        ->assertRedirect();

    expect($changeRequest->fresh()->status)->toBe(ChangeRequestStatus::Rejected);
});

it('does not change company data when a request is rejected', function () {
    $admin = adminUser();
    $owner = approvedOwner();
    $company = $owner->company;

    $originalName = $company->name;
    $originalIndustry = $company->industry;

    $changeRequest = CompanyChangeRequest::query()->create([
        'company_id' => $company->id,
        'new_name' => 'Rejected Name',
        'new_industry' => 'Rejected Industry',
        'status' => ChangeRequestStatus::Pending,
    ]);

    $this->actingAs($admin)
        ->post(route('company-change-requests.reject', $changeRequest))
        ->assertRedirect();

    expect($company->fresh()->name)->toBe($originalName)
        ->and($company->fresh()->industry)->toBe($originalIndustry);
});

it('stores the admin reviewer and review timestamp', function () {
    $admin = adminUser();
    $owner = approvedOwner();

    $changeRequest = CompanyChangeRequest::query()->create([
        'company_id' => $owner->company->id,
        'new_name' => 'Reviewed Company',
        'status' => ChangeRequestStatus::Pending,
    ]);

    $this->actingAs($admin)
        ->post(route('company-change-requests.approve', $changeRequest))
        ->assertRedirect();

    $reviewedRequest = $changeRequest->fresh();

    expect($reviewedRequest->reviewed_by)->toBe($admin->id)
        ->and($reviewedRequest->reviewed_at)->not->toBeNull();
});

it('prevents company owners from viewing the admin queue', function () {
    $owner = approvedOwner();

    $this->actingAs($owner)
        ->get(route('company-change-requests.index'))
        ->assertForbidden();
});

it('prevents company owners from approving change requests', function () {
    $owner = approvedOwner();

    $changeRequest = CompanyChangeRequest::query()->create([
        'company_id' => $owner->company->id,
        'new_name' => 'Unauthorized Change',
        'status' => ChangeRequestStatus::Pending,
    ]);

    $this->actingAs($owner)
        ->post(route('company-change-requests.approve', $changeRequest))
        ->assertForbidden();
});

it('prevents job seekers from viewing the admin queue', function () {
    $jobSeeker = User::factory()->create([
        'role' => UserRole::JobSeeker,
    ]);

    $this->actingAs($jobSeeker)
        ->get(route('company-change-requests.index'))
        ->assertForbidden();
});

it('prevents job seekers from rejecting change requests', function () {
    $admin = adminUser();
    $jobSeeker = User::factory()->create([
        'role' => UserRole::JobSeeker,
    ]);
    $owner = approvedOwner();

    $changeRequest = CompanyChangeRequest::query()->create([
        'company_id' => $owner->company->id,
        'new_name' => 'Unauthorized Change',
        'status' => ChangeRequestStatus::Pending,
    ]);

    $this->actingAs($jobSeeker)
        ->post(route('company-change-requests.reject', $changeRequest))
        ->assertForbidden();
});

it('prevents reviewing an already decided request', function () {
    $admin = adminUser();
    $owner = approvedOwner();

    $changeRequest = CompanyChangeRequest::query()->create([
        'company_id' => $owner->company->id,
        'new_name' => 'Already Approved',
        'status' => ChangeRequestStatus::Approved,
    ]);

    $this->actingAs($admin)
        ->post(route('company-change-requests.approve', $changeRequest))
        ->assertSessionHasErrors('change_request');
});