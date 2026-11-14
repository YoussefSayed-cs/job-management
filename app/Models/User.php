<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasUuids, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'profile_picture',
        'password',
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

    protected function profilePictureUrl(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->profile_picture) {
                return null;
            }

            /** @var FilesystemAdapter $disk */
            $disk = Storage::disk('public');

            return $disk->url($this->profile_picture);
        });
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
