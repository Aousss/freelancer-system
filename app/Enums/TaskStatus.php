<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Backlog = 'backlog';
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Review = 'review';
    case Done = 'done';

    public function label(): string
    {
        return match ($this) {
            self::Backlog => 'Backlog',
            self::Todo => 'To Do',
            self::InProgress => 'In Progress',
            self::Review => 'In Review',
            self::Done => 'Done',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Backlog => 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20',
            self::Todo => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
            self::InProgress => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            self::Review => 'bg-violet-500/10 text-violet-300 border-violet-500/20',
            self::Done => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        };
    }
}
