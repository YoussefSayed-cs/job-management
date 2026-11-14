<?php

namespace App\Http\Requests\User;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', User::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],

            'role' => [
                'nullable',
                Rule::in([UserRole::CompanyOwner->value, UserRole::JobSeeker->value]),
            ],

            'status' => [
                'nullable',
                Rule::in(array_column(AccountStatus::cases(), 'value')),
            ],
        ];
    }
}
