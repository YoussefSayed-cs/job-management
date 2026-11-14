<?php

use App\Models\JobCategory;
use App\Models\JobVacancy;

it('allows admin to create a category', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->post(route('categories.store'), [
            'name' => 'Software Engineering',
        ])
        ->assertRedirect(route('categories.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('job_categories', [
        'name' => 'Software Engineering', ]);

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
