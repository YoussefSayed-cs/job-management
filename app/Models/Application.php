<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Application extends Model
{
    use HasUuids;

    protected $table = 'applications';

    protected $fillable = [
        'job_vacancy_id',
        'company_id',
        'user_id',
        'status',
        'job_title',
        'company_name',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'submitted_at' => 'datetime',
        ];
    }

    // job_vacancy_id ممكن يبقى null لو الوظيفة اتمسحت (FR-67)
    public function jobVacancy(): BelongsTo
    {
        return $this->belongsTo(JobVacancy::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resumeSnapshot(): HasOne
    {
        return $this->hasOne(ResumeSnapshot::class);
    }

    public function aiAnalysis(): HasOne
    {
        return $this->hasOne(AiAnalysis::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class);
    }
}
