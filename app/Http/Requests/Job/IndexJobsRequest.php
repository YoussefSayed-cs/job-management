<?php

namespace App\Http\Requests\Job;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Shagalni\Shared\Enums\EmploymentType;
use Shagalni\Shared\Enums\ExperienceLevel;
use Shagalni\Shared\Enums\JobStatus;
use Shagalni\Shared\Enums\WorkLocationType;
use Shagalni\Shared\Models\JobVacancy;

class IndexJobsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', JobVacancy::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],

            'status' => ['nullable', Rule::enum(JobStatus::class)],

            'job_category_id' => ['nullable', 'uuid', Rule::exists('job_categories', 'id')],

            'employment_type' => ['nullable', Rule::enum(EmploymentType::class)],

            'work_location_type' => ['nullable', Rule::enum(WorkLocationType::class)],

            'experience_level' => ['nullable', Rule::enum(ExperienceLevel::class)],

        ];
    }
}
