<?php

namespace App\Http\Requests\Company;

use App\Enums\RegistrationStatus;
use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCompaniesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Company::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],

            'status' => [
                'nullable',
                Rule::in(array_column(RegistrationStatus::cases(), 'value')),
            ],
        ];
    }
}