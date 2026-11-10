<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class RejectCompanyRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        
        return $this->user()?->can('review', $this->route('company')) ?? false;
    }

    public function rules(): array
    {
        return [
            
            'rejection_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }
}