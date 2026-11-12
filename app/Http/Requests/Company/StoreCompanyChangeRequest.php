<?php

namespace App\Http\Requests\Company;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $company = $this->user()?->company;

        return $company !== null
            && $this->user()->can('createChangeRequest', $company);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'new_name' => is_string($this->new_name) ? trim($this->new_name) : $this->new_name,
            'new_industry' => is_string($this->new_industry) ? trim($this->new_industry) : $this->new_industry,
        ]);
    }

    public function rules(): array
    {
        return [
            'new_name' => ['nullable', 'required_without:new_industry', 'string', 'max:255'],
            'new_industry' => ['nullable', 'required_without:new_name', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_name.required_without' => 'Enter a new company name or industry.',
            'new_industry.required_without' => 'Enter a new company name or industry.',
        ];
    }
}
