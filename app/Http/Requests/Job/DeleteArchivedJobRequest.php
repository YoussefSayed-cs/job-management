<?php

namespace App\Http\Requests\Job;

use App\Models\JobVacancy;
use Illuminate\Foundation\Http\FormRequest;

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