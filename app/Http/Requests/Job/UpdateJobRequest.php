<?php

namespace App\Http\Requests\Job;

use App\Enums\EmploymentType;
use App\Enums\ExperienceLevel;
use App\Enums\WorkLocationType;
use App\Models\JobVacancy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        $job = $this->route('job');

        return $job instanceof JobVacancy && ($this->user()?->can('update', $job) ?? false);
           
    }

    public function rules(): array
    {
        return [
            'job_category_id' => ['required','uuid',Rule::exists('job_categories', 'id'),],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'requirements' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],   
            'employment_type' => ['required',Rule::enum(EmploymentType::class),],
            'work_location_type' => ['required',Rule::enum(WorkLocationType::class),],      
            'required_skills' => ['nullable', 'string'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0'],   
            'experience_level' => ['nullable',Rule::enum(ExperienceLevel::class),],
            'application_deadline' => ['nullable', 'date'],
            
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $min = $this->input('salary_min');
                $max = $this->input('salary_max');

                if (
                    $min !== null &&
                    $max !== null &&
                    is_numeric($min) &&
                    is_numeric($max) &&
                    (float) $max < (float) $min
                ) {
                    $validator->errors()->add(
                        'salary_max',
                        'The maximum salary must be greater than or equal to the minimum salary.'
                    );
                }
            },
        ];
    }
}