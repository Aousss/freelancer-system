<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'estimated_hours',
        'hourly_rate',
        'fixed_amount',
        'git_ref',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'estimated_hours' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
            'fixed_amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function getLoggedHoursAttribute(): float
    {
        $seconds = (int) $this->timeEntries()->whereNotNull('ended_at')->sum('duration_seconds');
        return round($seconds / 3600, 2);
    }

    public function getEffectiveHourlyRateAttribute(): ?float
    {
        if (!$this->fixed_amount || (float) $this->fixed_amount <= 0) {
            return $this->hourly_rate ? (float) $this->hourly_rate : null;
        }

        $hours = $this->logged_hours;
        if ($hours <= 0) {
            return (float) $this->fixed_amount;
        }

        return round((float) $this->fixed_amount / $hours, 2);
    }
}
