<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeSnapshot extends Model
{
    use HasUuids;

    protected $table = 'resume_snapshots';

    protected $fillable = [
        'application_id',
        'file_path',
        'original_name',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}