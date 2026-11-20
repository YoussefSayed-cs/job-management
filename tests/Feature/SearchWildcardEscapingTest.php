<?php

use Shagalni\Shared\Models\JobCategory;
use Shagalni\Shared\Models\User;

test('percent and underscore searches match only literal characters in users and applications', function () {
    $owner = approvedOwner();
    $percentApplicant = User::factory()->create(['full_name' => 'Percent% Applicant', 'email' => 'percent@example.test']);
    $underscoreApplicant = User::factory()->create(['full_name' => 'Under_score Applicant', 'email' => 'underscore@example.test']);
    $ordinaryApplicant = User::factory()->create(['full_name' => 'Ordinary Applicant', 'email' => 'ordinary@example.test']);

    makeApplicationFor($owner, $percentApplicant);
    makeApplicationFor($owner, $underscoreApplicant);
    makeApplicationFor($owner, $ordinaryApplicant);

    $admin = adminUser();

    $this->actingAs($admin)
        ->get(route('users.index', ['search' => '%']))
        ->assertOk()
        ->assertSee('Percent% Applicant')
        ->assertDontSee('Under_score Applicant')
        ->assertDontSee('Ordinary Applicant');

    $this->actingAs($admin)
        ->get(route('users.index', ['search' => '_']))
        ->assertOk()
        ->assertSee('Under_score Applicant')
        ->assertDontSee('Percent% Applicant')
        ->assertDontSee('Ordinary Applicant');

    $this->actingAs($owner)
        ->get(route('applications.index', ['search' => '%']))
        ->assertOk()
        ->assertSee('Percent% Applicant')
        ->assertDontSee('Under_score Applicant')
        ->assertDontSee('Ordinary Applicant');

    $this->actingAs($owner)
        ->get(route('applications.index', ['search' => '_']))
        ->assertOk()
        ->assertSee('Under_score Applicant')
        ->assertDontSee('Percent% Applicant')
        ->assertDontSee('Ordinary Applicant');
});

test('percent and underscore searches match only literal characters in companies and jobs', function () {
    $percentOwner = approvedOwner();
    $percentOwner->company->update(['name' => 'Percent% Company']);

    $underscoreOwner = approvedOwner();
    $underscoreOwner->company->update(['name' => 'Under_score Company']);

    $ordinaryOwner = approvedOwner();
    $ordinaryOwner->company->update(['name' => 'Ordinary Company']);

    $category = JobCategory::firstOrCreate(['name' => 'Wildcard Test']);

    foreach ([
        [$percentOwner, 'Percent% Role'],
        [$underscoreOwner, 'Under_score Role'],
        [$ordinaryOwner, 'Ordinary Role'],
    ] as [$owner, $title]) {
        $owner->company->jobVacancies()->create([
            'job_category_id' => $category->id,
            'title' => $title,
            'description' => 'A job used to test search.',
            'requirements' => 'Relevant experience.',
            'location' => 'Cairo',
            'employment_type' => 'full_time',
            'work_location_type' => 'on_site',
        ]);
    }

    $admin = adminUser();

    $this->actingAs($admin)
        ->get(route('companies.index', ['search' => '%']))
        ->assertOk()
        ->assertSee('Percent% Company')
        ->assertDontSee('Under_score Company')
        ->assertDontSee('Ordinary Company');

    $this->actingAs($admin)
        ->get(route('companies.index', ['search' => '_']))
        ->assertOk()
        ->assertSee('Under_score Company')
        ->assertDontSee('Percent% Company')
        ->assertDontSee('Ordinary Company');

    $this->actingAs($admin)
        ->get(route('jobs.index', ['search' => '%']))
        ->assertOk()
        ->assertSee('Percent% Role')
        ->assertDontSee('Under_score Role')
        ->assertDontSee('Ordinary Role');

    $this->actingAs($admin)
        ->get(route('jobs.index', ['search' => '_']))
        ->assertOk()
        ->assertSee('Under_score Role')
        ->assertDontSee('Percent% Role')
        ->assertDontSee('Ordinary Role');
});
