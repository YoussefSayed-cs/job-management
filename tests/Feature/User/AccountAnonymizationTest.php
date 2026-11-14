<?php

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Company;
use App\Models\JobCategory;
use App\Models\JobVacancy;
use App\Models\Resume;
use App\Models\SavedJob;
use App\Models\User;
use App\Services\AccountAnonymizationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function dueForAnonymization(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'role' => UserRole::JobSeeker,
        'deletion_requested_at' => now()->subDays(31),
    ], $attributes));
}

function anonymize(User $user): void
{
    app(AccountAnonymizationService::class)->anonymize($user);
}

function createCompany(User $owner): Company
{
    return Company::unguarded(fn () => Company::create([
        'owner_id' => $owner->id,
        'name' => 'Acme',
        'industry' => 'Software',
        'location' => 'Cairo',
        'registration_status' => 'approved',
    ]));
}

function createJob(?Company $company = null): JobVacancy
{
    if (! $company) {
        $owner = User::factory()->create(['role' => UserRole::CompanyOwner]);
        $company = createCompany($owner);
    }

    $category = JobCategory::unguarded(fn () => JobCategory::create([
        'name' => 'Category '.Str::random(8),
    ]));

    return JobVacancy::unguarded(fn () => JobVacancy::create([
        'company_id' => $company->id,
        'job_category_id' => $category->id,
        'title' => 'Laravel Developer',
        'description' => 'Description',
        'requirements' => 'Requirements',
        'location' => 'Cairo',
        'employment_type' => 'full_time',
        'work_location_type' => 'on_site',
        'status' => 'active',
    ]));
}

test('anonymization replaces the identity but keeps the id and role', function () {
    $user = dueForAnonymization([
        'full_name' => 'Ahmed Ali',
        'email' => 'ahmed@example.com',
        'phone' => '01000000000',
    ]);
    $id = $user->id;

    anonymize($user);
    $user->refresh();

    expect($user->id)->toBe($id)
        ->and($user->role)->toBe(UserRole::JobSeeker)
        ->and($user->full_name)->not->toBe('Ahmed Ali')
        ->and($user->email)->toBe("anonymized-{$id}@anonymized.invalid")
        ->and($user->phone)->toBe('')
        ->and($user->profile_picture)->toBeNull()
        ->and($user->email_verified_at)->toBeNull()
        ->and($user->status)->toBe(AccountStatus::Deactivated)
        ->and($user->anonymized_at)->not->toBeNull()
        ->and(Hash::check('password', $user->password))->toBeFalse();
});

test('a suspended account becomes deactivated after anonymization', function () {
    $user = dueForAnonymization(['status' => AccountStatus::Suspended]);

    anonymize($user);

    expect($user->refresh()->status)->toBe(AccountStatus::Deactivated);
});

test('anonymization removes the resume, saved jobs, notifications and files', function () {
    Storage::fake('public');
    Storage::fake('local');

    $user = dueForAnonymization(['profile_picture' => 'avatars/me.jpg']);
    Storage::disk('public')->put('avatars/me.jpg', 'x');
    Storage::disk('local')->put('resumes/cv.pdf', 'x');

    Resume::unguarded(fn () => Resume::create([
        'user_id' => $user->id,
        'file_path' => 'resumes/cv.pdf',
        'original_name' => 'cv.pdf',
        'file_size' => 1000,
        'file_type' => 'pdf',
    ]));

    $job = createJob();
    SavedJob::unguarded(fn () => SavedJob::create([
        'user_id' => $user->id,
        'job_vacancy_id' => $job->id,
    ]));

    DB::table('notifications')->insert([
        'id' => (string) Str::uuid(),
        'type' => 'test',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => '{}',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    anonymize($user);

    expect(Resume::where('user_id', $user->id)->exists())->toBeFalse()
        ->and(SavedJob::where('user_id', $user->id)->exists())->toBeFalse()
        ->and(DB::table('notifications')->where('notifiable_id', $user->id)->exists())->toBeFalse();

    // Use assertFalse(Storage::disk(...)->exists(...)) if assertMissing is unavailable
    $this->assertFalse(Storage::disk('public')->exists('avatars/me.jpg'));
    $this->assertFalse(Storage::disk('local')->exists('resumes/cv.pdf'));
});

test('applications of an anonymized job seeker are preserved', function () {
    $seeker = dueForAnonymization();
    $job = createJob();

    $application = Application::unguarded(fn () => Application::create([
        'job_vacancy_id' => $job->id,
        'company_id' => $job->company_id,
        'user_id' => $seeker->id,
        'status' => 'pending',
        'job_title' => $job->title,
        'company_name' => 'Acme',
        'submitted_at' => now(),
    ]));

    anonymize($seeker);

    $application->refresh();
    expect($application->user_id)->toBe($seeker->id)
        ->and($application->job_vacancy_id)->toBe($job->id)
        ->and($application->company_id)->toBe($job->company_id);
});

test('anonymizing a company owner deletes the company and jobs but keeps applications', function () {
    $owner = dueForAnonymization(['role' => UserRole::CompanyOwner]);
    $company = createCompany($owner);
    $job = createJob($company);
    $seeker = User::factory()->create(['role' => UserRole::JobSeeker]);

    $application = Application::unguarded(fn () => Application::create([
        'job_vacancy_id' => $job->id,
        'company_id' => $company->id,
        'user_id' => $seeker->id,
        'status' => 'pending',
        'job_title' => $job->title,
        'company_name' => $company->name,
        'submitted_at' => now(),
    ]));

    anonymize($owner);

    expect(Company::find($company->id))->toBeNull()
        ->and(JobVacancy::find($job->id))->toBeNull();

    $application->refresh();
    expect($application->job_vacancy_id)->toBeNull()
        ->and($application->company_id)->toBeNull()
        ->and($application->job_title)->toBe('Laravel Developer')
        ->and($application->company_name)->toBe('Acme')
        ->and($application->user_id)->toBe($seeker->id);
});

test('an account is not anonymized before the grace period ends', function () {
    $user = dueForAnonymization(['deletion_requested_at' => now()->subDays(10)]);

    expect(fn () => anonymize($user))->toThrow(LogicException::class);
    expect($user->refresh()->anonymized_at)->toBeNull();
});

test('an account without a deletion request is not anonymized', function () {
    $user = dueForAnonymization(['deletion_requested_at' => null]);

    expect(fn () => anonymize($user))->toThrow(LogicException::class);
});

test('the admin account cannot be anonymized', function () {
    $admin = dueForAnonymization(['role' => UserRole::Admin]);

    expect(fn () => anonymize($admin))->toThrow(LogicException::class);
    expect($admin->refresh()->anonymized_at)->toBeNull();
});

test('an already anonymized account cannot be anonymized twice', function () {
    $user = dueForAnonymization();
    anonymize($user);

    expect(fn () => anonymize($user->refresh()))->toThrow(LogicException::class);
});
