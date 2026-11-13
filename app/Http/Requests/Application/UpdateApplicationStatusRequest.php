<?php

namespace App\Http\Requests\Application;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'status' => ['required',Rule::enum(ApplicationStatus::class),
            Rule::notIn([ApplicationStatus::Pending->value,ApplicationStatus::Withdrawn->value,]),], 
            'message' => ['nullable','string', 'max:1000', ],
                
        ];
    }
}
