<?php

namespace App\Http\Requests\Job;

use App\Http\Requests\Job\Concerns\ValidatesSalaryRange;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Shagalni\Shared\Enums\EmploymentType;
use Shagalni\Shared\Enums\ExperienceLevel;
use Shagalni\Shared\Enums\WorkLocationType;
use Shagalni\Shared\Models\JobVacancy;

class StoreJobRequest extends FormRequest
{
    use ValidatesSalaryRange;

    public function authorize(): bool
    {
        return $this->user()?->can('create', JobVacancy::class) ?? false;
    }

    public function rules(): array
    {
        return [

            'job_category_id' => ['required', 'uuid', Rule::exists('job_categories', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'requirements' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'employment_type' => ['required',  Rule::enum(EmploymentType::class)],
            'work_location_type' => ['required', Rule::enum(WorkLocationType::class)],
            'required_skills' => ['nullable', 'string'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0'],
            'experience_level' => ['nullable', Rule::enum(ExperienceLevel::class)],
            'application_deadline' => ['nullable', 'date', 'after_or_equal:today'],

        ];
    }
}
