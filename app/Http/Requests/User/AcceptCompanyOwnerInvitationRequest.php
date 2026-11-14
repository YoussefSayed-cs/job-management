<?php

namespace App\Http\Requests\User;

use App\Concerns\PasswordValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class AcceptCompanyOwnerInvitationRequest extends FormRequest
{
    use PasswordValidationRules;

    public function authorize(): bool
    {
        // The signed link is the authorization; the service also checks the token.
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => $this->passwordRules(),
        ];
    }
}
