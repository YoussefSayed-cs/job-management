<?php

use Shagalni\Shared\Enums\ApplicationStatus;

it('shows platform analytics with charts to an admin', function () {
    $owner = approvedOwner();

    makeApplicationFor($owner, null, ApplicationStatus::Accepted);
    makeApplicationFor($owner, null, ApplicationStatus::Rejected);
    makeApplicationFor($owner);

    $this->actingAs(adminUser())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Platform overview')
        ->assertSee('data-chart', false)
        ->assertViewHas('totals', fn (array $totals) => $totals['applications'] === 3
            && $totals['acceptance_rate'] === 50.0)
        ->assertViewHas('distribution', fn (array $distribution) => $distribution['accepted'] === 1);
});

it('renders the admin dashboard when the platform has no data', function () {
    $this->actingAs(adminUser())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('No data yet')
        ->assertViewHas('totals', fn (array $totals) => $totals['acceptance_rate'] === null);
});

it('does not expose platform analytics to a company owner', function () {
    $owner = approvedOwner();
    makeApplicationFor($owner);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Platform overview')
        ->assertViewMissing('totals');

});
