<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasUuids;

    protected $table = 'companies';

    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'industry',
        'location',
        'website',
        'logo',
        'registration_status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'registration_status' => RegistrationStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function jobVacancies(): HasMany
    {
        return $this->hasMany(JobVacancy::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function changeRequests(): HasMany
    {
        return $this->hasMany(CompanyChangeRequest::class);
    }
}
