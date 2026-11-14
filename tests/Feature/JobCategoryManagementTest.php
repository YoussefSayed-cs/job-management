<?php

use App\Models\JobCategory;
use App\Models\JobVacancy;
use Illuminate\Support\Str;

// ------------------------------------------
// Category CRUD
// ------------------------------------------

it('allows admin to view category management', function () {
    $admin = adminUser();

    JobCategory::create([
        'name' => 'Software Engineering',
    ]);

    $this->actingAs($admin)
        ->get(route('categories.index'))
        ->assertOk()
        ->assertSee('Software Engineering');
});

it('allows admin to create a category', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->post(route('categories.store'), [
            'name' => 'Software Engineering',
        ])
        ->assertRedirect(route('categories.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('job_categories', [
        'name' => 'Software Engineering',
    ]);
});

it('prevents company owners from accessing category management', function () {
    $owner = approvedOwner();

    $this->actingAs($owner)
        ->get(route('categories.index'))
        ->assertForbidden();

    $this->actingAs($owner)
        ->post(route('categories.store'), [
            'name' => 'Software Engineering',
        ])
        ->assertForbidden();
});

it('prevents company owners from updating categories', function () {
    $owner = approvedOwner();

    $category = JobCategory::create([
        'name' => 'Software',
    ]);

    $this->actingAs($owner)
        ->put(route('categories.update', $category), [
            'name' => 'Updated Software',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('job_categories', [
        'id' => $category->id,
        'name' => 'Software',
    ]);
});

it('prevents company owners from deleting categories', function () {
    $owner = approvedOwner();

    $category = JobCategory::create([
        'name' => 'Software',
    ]);

    $this->actingAs($owner)
        ->delete(route('categories.destroy', $category))
        ->assertForbidden();

    $this->assertDatabaseHas('job_categories', [
        'id' => $category->id,
    ]);
});

// ------------------------------------------
// Category Validation
// ------------------------------------------

it('rejects duplicate category names', function () {
    $admin = adminUser();

    JobCategory::create([
        'name' => 'Software Engineering',
    ]);

    $this->actingAs($admin)
        ->post(route('categories.store'), [
            'name' => 'Software Engineering',
        ])
        ->assertSessionHasErrors('name');
});

it('rejects empty and whitespace-only category names', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->post(route('categories.store'), [
            'name' => '   ',
        ])
        ->assertSessionHasErrors('name');

    $this->assertDatabaseMissing('job_categories', [
        'name' => '',
    ]);
});

it('trims category names before storing them', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->post(route('categories.store'), [
            'name' => '  Software Engineering  ',
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('job_categories', [
        'name' => 'Software Engineering',
    ]);
});

it('allows admin to update a category', function () {
    $admin = adminUser();

    $category = JobCategory::create([
        'name' => 'Software',
    ]);

    $this->actingAs($admin)
        ->put(route('categories.update', $category), [
            'name' => 'Software Engineering',
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('job_categories', [
        'id' => $category->id,
        'name' => 'Software Engineering',
    ]);
});

it('allows a category to keep its current name during update', function () {
    $admin = adminUser();

    $category = JobCategory::create([
        'name' => 'Software',
    ]);

    $this->actingAs($admin)
        ->put(route('categories.update', $category), [
            'name' => 'Software',
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('job_categories', [
        'id' => $category->id,
        'name' => 'Software',
    ]);
});

it('rejects updating a category with another category existing name', function () {
    $admin = adminUser();

    $firstCategory = JobCategory::create([
        'name' => 'Software',
    ]);

    JobCategory::create([
        'name' => 'Data Engineering',
    ]);

    $this->actingAs($admin)
        ->put(route('categories.update', $firstCategory), [
            'name' => 'Data Engineering',
        ])
        ->assertSessionHasErrors('name');

    $this->assertDatabaseHas('job_categories', [
        'id' => $firstCategory->id,
        'name' => 'Software',
    ]);
});

// ------------------------------------------
// Category Deletion Rules
// ------------------------------------------

it('allows admin to delete an unused category', function () {
    $admin = adminUser();

    $category = JobCategory::create([
        'name' => 'Unused Category',
    ]);

    $this->actingAs($admin)
        ->delete(route('categories.destroy', $category))
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseMissing('job_categories', [
        'id' => $category->id,
    ]);
});

it('prevents deleting a category assigned to a job', function () {
    $admin = adminUser();
    $owner = approvedOwner();

    $category = JobCategory::create([
        'name' => 'Software',
    ]);

    JobVacancy::create([
        'company_id' => $owner->company->id,
        'job_category_id' => $category->id,
        'title' => 'Backend Developer',
        'description' => 'Build backend services.',
        'requirements' => 'PHP experience.',
        'location' => 'Cairo',
        'employment_type' => 'full_time',
        'work_location_type' => 'on_site',
    ]);

    $this->actingAs($admin)
        ->delete(route('categories.destroy', $category))
        ->assertSessionHasErrors('category');

    $this->assertDatabaseHas('job_categories', [
        'id' => $category->id,
    ]);
});

// ------------------------------------------
// Job Management Integration
// ------------------------------------------

it('rejects creating a job with a nonexistent category', function () {
    $owner = approvedOwner();

    $this->actingAs($owner)
        ->post(route('jobs.store'), [
            'job_category_id' => (string) Str::uuid(),
            'title' => 'Backend Developer',
            'description' => 'Build backend services.',
            'requirements' => 'PHP experience.',
            'location' => 'Cairo',
            'employment_type' => 'full_time',
            'work_location_type' => 'on_site',
        ])
        ->assertSessionHasErrors('job_category_id');
});

it('filters jobs by category', function () {
    $admin = adminUser();
    $owner = approvedOwner();

    $softwareCategory = JobCategory::create([
        'name' => 'Software',
    ]);

    $dataCategory = JobCategory::create([
        'name' => 'Data Engineering',
    ]);

    JobVacancy::create([
        'company_id' => $owner->company->id,
        'job_category_id' => $softwareCategory->id,
        'title' => 'Backend Developer',
        'description' => 'Build backend services.',
        'requirements' => 'PHP experience.',
        'location' => 'Cairo',
        'employment_type' => 'full_time',
        'work_location_type' => 'on_site',
    ]);

    JobVacancy::create([
        'company_id' => $owner->company->id,
        'job_category_id' => $dataCategory->id,
        'title' => 'Analytics Pipeline Specialist',
        'description' => 'Build data pipelines.',
        'requirements' => 'SQL experience.',
        'location' => 'Cairo',
        'employment_type' => 'full_time',
        'work_location_type' => 'remote',
    ]);

    $this->actingAs($admin)
        ->get(route('jobs.index', [
            'job_category_id' => $softwareCategory->id,
        ]))
        ->assertOk()
        ->assertSee('Backend Developer')
        ->assertDontSee('Analytics Pipeline Specialist');
});
