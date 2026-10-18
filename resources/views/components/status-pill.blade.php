@props([
    'variant' => 'emerald', // emerald, violet, amber, rose, zinc, blue
    'size' => 'sm',
])

@php
$classes = match($variant) {
    'emerald' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    'violet' => 'bg-violet-500/10 text-violet-300 border-violet-500/20',
    'amber' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    'rose' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
    'blue' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
    'zinc' => 'bg-zinc-800 text-zinc-400 border-zinc-700/60',
    default => 'bg-zinc-800 text-zinc-400 border-zinc-700/60',
};

$sizeClasses = match($size) {
    'xs' => 'px-2 py-0.5 text-[10px]',
    'sm' => 'px-2.5 py-1 text-xs',
    'md' => 'px-3 py-1.5 text-sm',
    default => 'px-2.5 py-1 text-xs',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 font-medium rounded-full border {$classes} {$sizeClasses}"]) }}>
    {{ $slot }}
</span>
