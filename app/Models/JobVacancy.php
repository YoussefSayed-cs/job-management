<?php

namespace App\Models;

use App\Enums\EmploymentType;
use App\Enums\ExperienceLevel;
use App\Enums\JobStatus;
use App\Enums\WorkLocationType;
use App\Enums\AccountStatus;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class JobVacancy extends Model
{
    use HasUuids;

    protected $table = 'job_vacancies';

    protected $fillable = [
        'company_id',
        'job_category_id',
        'title',
        'description',
        'requirements',
        'location',
        'employment_type',
        'work_location_type',
        'required_skills',
        'salary_min',
        'salary_max',
        'experience_level',
        'application_deadline',
        'status',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'employment_type' => EmploymentType::class,
            'work_location_type' => WorkLocationType::class,
            'experience_level' => ExperienceLevel::class,
            'status' => JobStatus::class,
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
            'application_deadline' => 'date',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * Determine whether the application deadline has passed.
     *
     * Expired is derived from application_deadline.
     * It is not stored as a Job status.
     */
    public function isExpired(): bool
    {
        if ($this->application_deadline === null) {
            return false;
        }

        return Carbon::parse($this->application_deadline)->endOfDay()->isPast();
    }

    /**
     * Single eligibility rule used for public visibility and applying.
     * Active job + deadline not passed + Approved company + Active owner.
     */
    public function isAcceptingApplications(): bool
    {
        if ($this->status !== JobStatus::Active || $this->isExpired()) {
            return false;
        }

        $company = $this->company;

        return $company !== null
            && $company->registration_status === RegistrationStatus::Approved
            && $company->owner?->status === AccountStatus::Active;
    }

    /**
     * Query version of isAcceptingApplications() for lists and detail pages.
     *
     * @param  Builder<JobVacancy>  $query
     */
    public function scopePubliclyVisible(Builder $query): void
    {
        $query->where('status', JobStatus::Active->value)
            ->where(function (Builder $query) {
                $query->whereNull('application_deadline')
                    ->orWhereDate('application_deadline', '>=', now()->toDateString());
            })
            ->whereHas('company', function (Builder $company) {
                $company->where('registration_status', RegistrationStatus::Approved->value)
                    ->whereHas('owner', function (Builder $owner) {
                        $owner->where('status', AccountStatus::Active->value);
                    });
            });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'job_category_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function savedJobs(): HasMany
    {
        return $this->hasMany(SavedJob::class);
    }
}
