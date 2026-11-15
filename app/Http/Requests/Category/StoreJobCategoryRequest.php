<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Shagalni\Shared\Models\JobCategory;

class StoreJobCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', JobCategory::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name)
                ? trim($this->name)
                : $this->name,
        ]);
    }

    public function rules(): array
    {
        return [

            'name' => ['required', 'string', 'max:255', 'unique:job_categories,name'],

        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.unique' => 'This category already exists.',
        ];
    }
}
