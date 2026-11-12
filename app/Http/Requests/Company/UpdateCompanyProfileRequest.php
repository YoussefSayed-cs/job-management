<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $company = $this->user()?->company;

        return $company !== null
            && $this->user()->can('updateProfile', $company);
    }

    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string', 'max:5000',],

            'location' => ['required', 'string', 'max:255',],

            'website' => ['nullable', 'url', 'max:255',],

            'logo' => ['nullable',  'image',  'mimes:jpg,jpeg,png,webp', 'max:2048',],

            // These fields require Admin approval.
            'name' => ['prohibited',],

            'industry' => ['prohibited', ],
                
        ];
    }
}
