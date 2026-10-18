<div class="space-y-6">
    <!-- Welcome Header & Quick Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                Mission Control
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono bg-violet-500/10 text-violet-300 border border-violet-500/20">
                    Bento OS
                </span>
            </h1>
            <p class="text-xs text-zinc-400 mt-0.5">High-contrast solo engineer workbench & financial pulse.</p>
        </div>

        <div class="flex items-center gap-2">
            <a
                href="/timesheet"
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-[#171A24] border border-white/[0.08] hover:border-violet-500/30 text-xs font-medium text-zinc-300 hover:text-white transition-all shadow-sm"
            >
                <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Manual Log</span>
            </a>

            <a
                href="/invoices/new"
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-semibold shadow-lg shadow-violet-600/20 active:scale-95 transition-all"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Create Invoice</span>
            </a>
        </div>
    </div>

    <!-- ROW 1: 4 KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Hours Logged (This Week) -->
        <x-stat-card
            title="Weekly Effort"
            :value="$thisWeekHours . 'h'"
            :subtitle="'Target: ' . $weeklyTargetHours . 'h (' . $weekProgressPct . '% reached)'"
            accent="violet"
            trend="{{ $weekProgressPct >= 70 ? 'On Pace' : 'Behind Pace' }}"
            trendType="{{ $weekProgressPct >= 70 ? 'up' : 'neutral' }}"
        >
            <x-slot:icon>
                <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <!-- 2. Unbilled Revenue Pot -->
        <x-stat-card
            title="Unbilled Revenue Pot"
            :value="'RM ' . number_format($unbilledRevenue, 2)"
            subtitle="Ready to aggregate into invoice"
            accent="amber"
            trend="Accruing Live"
            trendType="up"
        >
            <x-slot:icon>
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <!-- 3. Effective Hourly Rate -->
        <x-stat-card
            title="Effective Hourly Rate"
            :value="'RM ' . number_format($effectiveHourlyRate, 2) . '/h'"
            subtitle="Blended rate across all projects"
            accent="emerald"
            trend="Healthy Margin"
            trendType="up"
        >
            <x-slot:icon>
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <!-- 4. Sprint Velocity -->
        <x-stat-card
            title="Sprint Velocity"
            :value="$completedThisWeek . ' Done'"
            :subtitle="$inReviewCount . ' tasks pending review'"
            accent="blue"
            trend="Active Cycle"
            trendType="neutral"
        >
            <x-slot:icon>
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    <!-- ROW 2: Project Burn-Rate Meters & Quick Start Tasks -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Project Burn Bars -->
        <div class="lg:col-span-2 rounded-2xl bg-[#12141C] border border-white/[0.07] p-5 shadow-lg shadow-black/30">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-white">Project Budget Burn Rates</h2>
                    <p class="text-xs text-zinc-400">Logged effort vs estimated hour caps.</p>
                </div>
                <a href="/projects" class="text-xs font-medium text-violet-400 hover:text-violet-300">View All →</a>
            </div>

            <div class="space-y-3">
                @forelse($activeProjects as $project)
                    <x-project-burn-meter :project="$project" />
                @empty
                    <div class="text-center py-8 text-zinc-500 text-xs">
                        No active projects found.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right: In-Progress / Quick Launch Task Cards -->
        <div class="rounded-2xl bg-[#12141C] border border-white/[0.07] p-5 shadow-lg shadow-black/30 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-white">Sprint Tasks</h2>
                        <p class="text-xs text-zinc-400">One-click timer launch.</p>
                    </div>
                    <span class="text-[11px] font-mono text-zinc-500">READY</span>
                </div>

                <div class="space-y-2">
                    @forelse($inProgressTasks as $task)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#171A24] border border-white/[0.05] hover:border-violet-500/30 transition-all group">
                            <div class="min-w-0 flex-1 pr-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $task->project->color }}"></span>
                                    <span class="text-[11px] text-zinc-400 truncate">{{ $task->project->name }}</span>
                                </div>
                                <div class="text-xs font-medium text-zinc-200 group-hover:text-white truncate mt-0.5">
                                    {{ $task->title }}
                                </div>
                                @if($task->git_ref)
                                    <div class="text-[10px] font-mono text-zinc-500 mt-0.5"># {{ $task->git_ref }}</div>
                                @endif
                            </div>

                            <button
                                type="button"
                                wire:click="startTaskTimer({{ $task->project_id }}, {{ $task->id }}, '{{ addslashes($task->title) }}', '{{ $task->git_ref }}')"
                                title="Start timer on this task"
                                class="h-7 w-7 rounded-lg bg-emerald-500/10 hover:bg-emerald-500 hover:text-black text-emerald-400 border border-emerald-500/20 flex items-center justify-center transition-all cursor-pointer flex-shrink-0"
                            >
                                <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </button>
                        </div>
                    @empty
                        <div class="text-center py-6 text-zinc-500 text-xs">
                            No sprint tasks pending.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-white/[0.05] text-center">
                <span class="text-[11px] text-zinc-500 font-mono">Tip: Use ⌘K to start timers from anywhere</span>
            </div>
        </div>
    </div>

    <!-- ROW 3: Today's Timesheet Table -->
    <div class="rounded-2xl bg-[#12141C] border border-white/[0.07] p-5 shadow-lg shadow-black/30">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-semibold text-white">Today's Timesheet Activity</h2>
                <p class="text-xs text-zinc-400">Chronological work logs and resume triggers for today.</p>
            </div>
            <a href="/timesheet" class="text-xs font-medium text-violet-400 hover:text-violet-300">Detailed Manager →</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-white/[0.07] text-zinc-400 uppercase font-semibold text-[10px] tracking-wider">
                        <th class="py-2.5 px-3">Project</th>
                        <th class="py-2.5 px-3">Description / Git Ref</th>
                        <th class="py-2.5 px-3">Time Range</th>
                        <th class="py-2.5 px-3 text-right">Duration</th>
                        <th class="py-2.5 px-3 text-right">Billable</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($todayEntries as $entry)
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full" style="background-color: {{ $entry->project->color }}"></span>
                                    <span class="font-medium text-zinc-200">{{ $entry->project->name }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <div class="text-zinc-200 font-medium">{{ $entry->description }}</div>
                                @if($entry->git_ref)
                                    <span class="text-[10px] font-mono text-zinc-500">git: {{ $entry->git_ref }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap text-zinc-400 font-mono text-[11px]" data-mono>
                                {{ $entry->started_at->format('H:i') }}
                                →
                                {{ $entry->ended_at ? $entry->ended_at->format('H:i') : 'Running' }}
                            </td>
                            <td class="py-3 px-3 text-right whitespace-nowrap font-mono font-medium {{ $entry->ended_at ? 'text-zinc-200' : 'text-emerald-400' }}" data-mono>
                                {{ $entry->formatted_duration }}
                            </td>
                            <td class="py-3 px-3 text-right whitespace-nowrap font-mono text-zinc-300" data-mono>
                                @if($entry->is_billable)
                                    RM {{ number_format($entry->billable_amount, 2) }}
                                @else
                                    <span class="text-zinc-600">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                @if($entry->is_invoiced)
                                    <x-status-pill variant="amber" size="xs">
                                        🔒 {{ $entry->invoice->invoice_number ?? 'INVOICED' }}
                                    </x-status-pill>
                                @elseif($entry->ended_at === null)
                                    <x-status-pill variant="emerald" size="xs">
                                        <span class="animate-pulse">● Active</span>
                                    </x-status-pill>
                                @elseif($entry->is_billable)
                                    <x-status-pill variant="violet" size="xs">
                                        Unbilled
                                    </x-status-pill>
                                @else
                                    <x-status-pill variant="zinc" size="xs">
                                        Non-billable
                                    </x-status-pill>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right whitespace-nowrap">
                                @if($entry->ended_at !== null)
                                    <button
                                        type="button"
                                        wire:click="resumeEntry({{ $entry->id }})"
                                        title="Resume this task entry"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white font-mono text-xs transition-colors cursor-pointer"
                                    >
                                        <span>↻</span>
                                        <span class="text-[10px]">Resume</span>
                                    </button>
                                @else
                                    <span class="text-emerald-400 font-mono text-[10px]">CURRENT</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-zinc-500">
                                No time entries logged today yet. Start a timer from the top bar!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
