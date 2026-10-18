<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'task_id',
        'invoice_id',
        'description',
        'git_ref',
        'started_at',
        'ended_at',
        'duration_seconds',
        'is_billable',
        'is_paused',
        'hourly_rate_snapshot',
        'billable_amount',
        'is_invoiced',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'duration_seconds' => 'integer',
            'is_billable' => 'boolean',
            'is_paused' => 'boolean',
            'hourly_rate_snapshot' => 'decimal:2',
            'billable_amount' => 'decimal:2',
            'is_invoiced' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (TimeEntry $entry) {
            if ($entry->getOriginal('is_invoiced') === true) {
                // Allow only explicit unlocking / detaching from voided or deleted invoice
                $isDetaching = $entry->isDirty('is_invoiced')
                    && $entry->is_invoiced === false
                    && is_null($entry->invoice_id);

                if (!$isDetaching) {
                    throw new DomainException('Locked invoiced time entries cannot be modified.');
                }
            }
        });

        static::deleting(function (TimeEntry $entry) {
            if ($entry->getOriginal('is_invoiced') === true) {
                throw new DomainException('Locked invoiced time entries cannot be modified.');
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function scopeRunning(Builder $query): Builder
    {
        return $query->whereNull('ended_at');
    }

    public function scopeUnbilled(Builder $query): Builder
    {
        return $query->where('is_billable', true)
            ->where('is_invoiced', false)
            ->whereNotNull('ended_at');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('started_at', today());
    }

    public function getElapsedSecondsAttribute(): int
    {
        if ($this->is_paused) {
            return (int) $this->duration_seconds;
        }

        if ($this->ended_at === null && $this->started_at !== null) {
            return (int) $this->duration_seconds + max(0, $this->started_at->diffInSeconds(now()));
        }

        return (int) $this->duration_seconds;
    }

    public function getFormattedDurationAttribute(): string
    {
        $seconds = $this->elapsed_seconds;

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    public function getFormattedHoursAttribute(): string
    {
        $seconds = $this->elapsed_seconds;
        return number_format($seconds / 3600, 2) . 'h';
    }
}
