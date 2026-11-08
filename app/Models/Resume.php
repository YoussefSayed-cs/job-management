<?php

namespace App\Models;

use App\Enums\ResumeFileType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resume extends Model
{
    use HasUuids;

    protected $table = 'resumes';

    protected $fillable = [
        'user_id',
        'file_path',
        'original_name',
        'file_size',
        'file_type',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'file_type' => ResumeFileType::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}