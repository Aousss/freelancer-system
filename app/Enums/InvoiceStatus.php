<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Void = 'void';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Sent => 'Sent',
            self::Paid => 'Paid',
            self::Overdue => 'Overdue',
            self::Void => 'Void',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20',
            self::Sent => 'bg-violet-500/10 text-violet-300 border-violet-500/20',
            self::Paid => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            self::Overdue => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
            self::Void => 'bg-zinc-800 text-zinc-500 border-zinc-700/50',
        };
    }
}
