@props([
    'project',
])

@php
$logged = $project->logged_hours;
$budget = (float) ($project->budget_hours ?? 0);
$pct = $project->burn_percentage;
$barColor = match(true) {
    $pct >= 95 => 'bg-rose-500 shadow-[0_0_12px_rgba(244,63,94,0.4)]',
    $pct >= 75 => 'bg-amber-500 shadow-[0_0_12px_rgba(245,158,11,0.4)]',
    default => 'bg-violet-500 shadow-[0_0_12px_rgba(139,92,246,0.3)]',
};

$badgeVariant = match(true) {
    $pct >= 95 => 'rose',
    $pct >= 75 => 'amber',
    default => 'violet',
};
@endphp

<div class="rounded-xl bg-[#171A24] border border-white/[0.06] p-4 hover:border-white/[0.1] transition-all">
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-2 min-w-0">
            <span class="h-2.5 w-2.5 rounded-full flex-shrink-0" style="background-color: {{ $project->color ?? '#8b5cf6' }}"></span>
            <a href="/projects/{{ $project->slug }}" class="font-semibold text-sm text-zinc-100 hover:text-white truncate hover:underline">
                {{ $project->name }}
            </a>
            <span class="text-xs text-zinc-500 truncate">· {{ $project->client->name ?? 'Client' }}</span>
        </div>
        <x-status-pill :variant="$badgeVariant" size="xs">
            <span class="font-mono" data-mono>{{ $pct }}%</span>
        </x-status-pill>
    </div>

    <!-- Progress bar -->
    <div class="h-2 w-full bg-zinc-800/80 rounded-full overflow-hidden p-0.5">
        <div class="h-full rounded-full transition-all duration-500 {{ $barColor }}" style="width: {{ min(100, $pct) }}%;"></div>
    </div>

    <div class="mt-2 flex items-center justify-between text-xs text-zinc-400">
        <span class="font-mono" data-mono>
            <span class="text-zinc-200 font-medium">{{ $logged }}h</span> / {{ $budget > 0 ? "{$budget}h" : 'No limit' }}
        </span>
        <span class="text-[11px] text-zinc-500 uppercase tracking-wider">
            {{ $project->billing_type->label() ?? 'Hourly' }}
        </span>
    </div>
</div>
