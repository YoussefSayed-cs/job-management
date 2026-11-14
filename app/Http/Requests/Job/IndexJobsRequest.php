<?php

namespace App\Http\Requests\Job;

use App\Enums\EmploymentType;
use App\Enums\ExperienceLevel;
use App\Enums\JobStatus;
use App\Enums\WorkLocationType;
use App\Models\JobVacancy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
