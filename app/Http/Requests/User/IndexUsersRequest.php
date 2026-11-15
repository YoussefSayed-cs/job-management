<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Shagalni\Shared\Enums\AccountStatus;
use Shagalni\Shared\Enums\UserRole;
use Shagalni\Shared\Models\User;

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
