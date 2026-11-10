<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasUuids, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'full_name',
        'email',
        'email_verified_at',
        'phone',
        'profile_picture',
        'password',
        'role',
        'status',
        'deletion_requested_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'deletion_requested_at' => 'datetime',
            'anonymized_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => AccountStatus::class,
        ];
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class, 'owner_id');
    }

    public function resume(): HasOne
    {
        return $this->hasOne(Resume::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function savedJobs(): HasMany
    {
        return $this->hasMany(SavedJob::class);
    }
}