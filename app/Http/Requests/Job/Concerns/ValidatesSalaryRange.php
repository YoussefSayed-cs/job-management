<?php

namespace App\Http\Requests\Job\Concerns;

use Illuminate\Validation\Validator;

trait ValidatesSalaryRange
{
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $min = $this->input('salary_min');
                $max = $this->input('salary_max');

                if (
                    $min !== null &&
                    $max !== null &&
                    is_numeric($min) &&
                    is_numeric($max) &&
                    (float) $max < (float) $min
                ) {
                    $validator->errors()->add(
                        'salary_max',
                        'The maximum salary must be greater than or equal to the minimum salary.'
                    );
                }
            },
        ];
    }
}
