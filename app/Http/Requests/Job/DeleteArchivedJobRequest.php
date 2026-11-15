<?php

namespace App\Http\Requests\Job;

use Illuminate\Foundation\Http\FormRequest;
use Shagalni\Shared\Models\JobVacancy;

class DeleteArchivedJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        $job = $this->route('job');

        return $job instanceof JobVacancy
            && ($this->user()?->can('delete', $job) ?? false);
    }

    public function rules(): array
    {
        return [
            'confirmation' => ['required', 'accepted'],
        ];
    }
}
