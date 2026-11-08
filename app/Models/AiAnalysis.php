<?php

namespace App\Models;

use App\Enums\AiAnalysisStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAnalysis extends Model
{
    use HasUuids;

    protected $table = 'ai_analyses';

    protected $fillable = [
        'application_id',
        'status',
        'match_score',
        'feedback',
        'attempts',
        'failure_reason',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AiAnalysisStatus::class,
            'match_score' => 'integer',
            'attempts' => 'integer',
            'analyzed_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}