<?php

namespace App\Http\Requests\Application;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Shagalni\Shared\Enums\ApplicationStatus;
use Shagalni\Shared\Models\Application;

class UpdateApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $application = $this->route('application');

        return $application instanceof Application
            && ($this->user()?->can('updateStatus', $application) ?? false);
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    ApplicationStatus::UnderReview->value,
                    ApplicationStatus::Shortlisted->value,
                    ApplicationStatus::Interview->value,
                    ApplicationStatus::Accepted->value,
                    ApplicationStatus::Rejected->value,
                ]),
            ],

            'message' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
