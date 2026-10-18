<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Completed = 'completed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Paused => 'Paused',
            self::Completed => 'Completed',
            self::Archived => 'Archived',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            self::Paused => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            self::Completed => 'bg-violet-500/10 text-violet-300 border-violet-500/20',
            self::Archived => 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20',
        };
    }
}
