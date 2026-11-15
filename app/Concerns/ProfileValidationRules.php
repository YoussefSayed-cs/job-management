<?php

namespace App\Concerns;

use Illuminate\Validation\Rule;
use Shagalni\Shared\Models\User;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function profileRules(?string $userId = null): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($userId),
            ],

            'phone' => [
                'required',
                'string',
                'max:20',
            ],
        ];
    }
}
