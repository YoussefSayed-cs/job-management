<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobCategory extends Model
{
    use HasUuids;

    protected $table = 'job_categories';

    protected $fillable = ['name'];

    public function jobVacancies(): HasMany
    {
        return $this->hasMany(JobVacancy::class);
    }
}
