<?php

it('preserves omitted optional fields when partially updating the company profile', function () {
    $owner = approvedOwner();
    $company = $owner->company;

    $company->update([
        'description' => 'Existing company description.',
        'website' => 'https://existing.example.com',
    ]);

    $this->actingAs($owner)
        ->patch(route('my-company.update'), [
            'location' => 'Alexandria',
        ])
        ->assertRedirect(route('my-company.show'));

    expect($company->fresh()->description)->toBe('Existing company description.')
        ->and($company->fresh()->website)->toBe('https://existing.example.com')
        ->and($company->fresh()->location)->toBe('Alexandria');
});
