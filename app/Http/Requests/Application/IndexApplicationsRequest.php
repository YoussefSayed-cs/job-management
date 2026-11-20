<?php

namespace App\Http\Requests\Application;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Shagalni\Shared\Enums\ApplicationStatus;
use Shagalni\Shared\Models\Application;

class IndexApplicationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Application::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(ApplicationStatus::class)],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }
}
