<?php

namespace App\Models;

use App\Enums\BillingType;
use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'slug',
        'billing_type',
        'hourly_rate',
        'fixed_budget_amount',
        'monthly_retainer_amount',
        'budget_hours',
        'color',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'billing_type' => BillingType::class,
            'status' => ProjectStatus::class,
            'hourly_rate' => 'decimal:2',
            'fixed_budget_amount' => 'decimal:2',
            'monthly_retainer_amount' => 'decimal:2',
            'budget_hours' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getLoggedHoursAttribute(): float
    {
        $seconds = (int) $this->timeEntries()->whereNotNull('ended_at')->sum('duration_seconds');
        return round($seconds / 3600, 2);
    }

    public function getBurnPercentageAttribute(): float
    {
        if (!$this->budget_hours || (float) $this->budget_hours <= 0) {
            return 0.0;
        }

        return round(min(150, ($this->logged_hours / (float) $this->budget_hours) * 100), 1);
    }

    public function getBurnColorClassAttribute(): string
    {
        $pct = $this->burn_percentage;
        if ($pct >= 95) {
            return 'bg-rose-500';
        }
        if ($pct >= 75) {
            return 'bg-amber-500';
        }
        return 'bg-violet-500';
    }

    /**
     * Compute Effective Hourly Rate based on the project's flexible billing model:
     * 1. Per Hour: returns hourly rate
     * 2. Per Task: sum of task fixed amounts / logged hours
     * 3. Per Project: fixed_budget_amount / logged hours
     * 4. Monthly: monthly_retainer_amount / logged hours
     */
    public function getEffectiveHourlyRateAttribute(): float
    {
        $logged = max(0.1, $this->logged_hours);

        return match ($this->billing_type) {
            BillingType::PerHour, BillingType::Hourly => (float) ($this->hourly_rate ?? $this->client?->default_rate ?? 0.00),
            BillingType::PerProject, BillingType::Fixed => round((float) ($this->fixed_budget_amount ?? 0) / $logged, 2),
            BillingType::Monthly, BillingType::Retainer => round((float) ($this->monthly_retainer_amount ?? $this->fixed_budget_amount ?? 0) / $logged, 2),
            BillingType::PerTask => round((float) $this->tasks()->sum('fixed_amount') / $logged, 2),
        };
    }

    /**
     * Display rate summary according to billing model
     */
    public function getBillingRateSummaryAttribute(): string
    {
        $currency = $this->client->currency ?? 'MYR';

        return match ($this->billing_type) {
            BillingType::PerHour, BillingType::Hourly => "{$currency} " . number_format($this->hourly_rate ?? $this->client?->default_rate ?? 0, 2) . "/h",
            BillingType::PerTask => "Per Task (Variable)",
            BillingType::PerProject, BillingType::Fixed => "{$currency} " . number_format($this->fixed_budget_amount ?? 0, 2) . " Fixed",
            BillingType::Monthly, BillingType::Retainer => "{$currency} " . number_format($this->monthly_retainer_amount ?? $this->fixed_budget_amount ?? 0, 2) . "/mo",
        };
    }
}
