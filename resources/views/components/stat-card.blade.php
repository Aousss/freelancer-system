@props([
    'title',
    'value',
    'subtitle' => null,
    'trend' => null,
    'trendType' => 'neutral', // up, down, neutral
    'icon' => null,
    'accent' => 'violet', // violet, emerald, amber, rose, blue
])

@php
$accentBg = match($accent) {
    'emerald' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    'violet' => 'bg-violet-500/10 text-violet-400 border-violet-500/20',
    'amber' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    'rose' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
    'blue' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
    default => 'bg-violet-500/10 text-violet-400 border-violet-500/20',
};
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl bg-[#12141C] border border-white/[0.07] p-5 shadow-lg shadow-black/40 hover:border-white/[0.12] transition-all duration-200 group']) }}>
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-zinc-400">{{ $title }}</p>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-white font-mono" data-mono>{{ $value }}</span>
            </div>
            @if($subtitle)
                <p class="mt-1 text-xs text-zinc-400">{{ $subtitle }}</p>
            @endif
        </div>
        @if($icon)
            <div class="flex h-10 w-10 items-center justify-center rounded-xl border {{ $accentBg }}">
                {{ $icon }}
            </div>
        @endif
    </div>

    @if($trend)
        <div class="mt-3 flex items-center gap-1.5 text-xs">
            <span class="font-medium {{ $trendType === 'up' ? 'text-emerald-400' : ($trendType === 'down' ? 'text-rose-400' : 'text-zinc-400') }}">
                {{ $trend }}
            </span>
        </div>
    @endif

    <!-- Subtle gradient accent corner glow -->
    <div class="absolute -right-6 -bottom-6 h-24 w-24 rounded-full bg-white/[0.02] blur-xl pointer-events-none group-hover:bg-white/[0.04] transition-all duration-300"></div>
</div>
