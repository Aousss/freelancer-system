<?php

namespace App\Enums;

enum BillingType: string
{
    case PerHour = 'per_hour';
    case PerTask = 'per_task';
    case PerProject = 'per_project';
    case Monthly = 'monthly';

    // Backward compatibility aliases
    case Hourly = 'hourly';
    case Fixed = 'fixed';
    case Retainer = 'retainer';

    public function label(): string
    {
        return match ($this) {
            self::PerHour, self::Hourly => 'Per Hour',
            self::PerTask => 'Per Task',
            self::PerProject, self::Fixed => 'Per Project',
            self::Monthly, self::Retainer => 'Monthly Retainer',
        };
    }

    public function shortCode(): string
    {
        return match ($this) {
            self::PerHour, self::Hourly => '1-Hour',
            self::PerTask => '2-Task',
            self::PerProject, self::Fixed => '3-Project',
            self::Monthly, self::Retainer => '4-Monthly',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::PerHour, self::Hourly => 'violet',
            self::PerTask => 'blue',
            self::PerProject, self::Fixed => 'emerald',
            self::Monthly, self::Retainer => 'amber',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PerHour, self::Hourly => 'Track elapsed time; billed as Hours × Hourly Rate.',
            self::PerTask => 'Fixed deliverable fee per task; tracks time to gauge effective hourly rate.',
            self::PerProject => 'Fixed lump-sum project budget; tracks overall effort against budget cap.',
            self::Monthly => 'Recurring monthly retainer fee with an allocated hours allowance.',
        };
    }
}
