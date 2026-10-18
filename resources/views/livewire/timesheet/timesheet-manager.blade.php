<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                Timesheet Manager
            </h1>
            <p class="text-xs text-zinc-400 mt-0.5">Filter, audit, resume, or manually record developer time logs.</p>
        </div>

        <button
            type="button"
            wire:click="openManualModal"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-semibold shadow-lg shadow-violet-600/20 active:scale-95 transition-all self-start sm:self-auto cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <span>Log Time (Natural Language)</span>
        </button>
    </div>

    <!-- Alert Messages -->
    @if($successMessage)
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-xs text-emerald-300 flex items-center justify-between">
            <span>{{ $successMessage }}</span>
            <button type="button" wire:click="$set('successMessage', '')" class="text-emerald-400 hover:text-emerald-200">✕</button>
        </div>
    @endif

    @if($errorMessage)
        <div class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-xs text-rose-300 flex items-center justify-between">
            <span>{{ $errorMessage }}</span>
            <button type="button" wire:click="$set('errorMessage', '')" class="text-rose-400 hover:text-rose-200">✕</button>
        </div>
    @endif

    <!-- Filter & Search Toolbar -->
    <div class="p-4 rounded-2xl bg-[#12141C] border border-white/[0.07] flex flex-col md:flex-row items-center justify-between gap-3 shadow-lg shadow-black/20">
        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
            <!-- Project Filter -->
            <select
                wire:model.live="filterProjectId"
                class="bg-[#171A24] text-xs font-medium text-zinc-200 border border-white/[0.08] rounded-xl px-3 py-2 focus:outline-none focus:border-violet-500 transition-colors"
            >
                <option value="">All Projects</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>

            <!-- Billing State Segmented Tabs -->
            <div class="flex items-center bg-[#171A24] p-1 rounded-xl border border-white/[0.08] text-xs">
                <button
                    type="button"
                    wire:click="$set('filterBilling', 'all')"
                    class="px-3 py-1 rounded-lg transition-all {{ $filterBilling === 'all' ? 'bg-violet-600 text-white font-medium shadow' : 'text-zinc-400 hover:text-zinc-200' }}"
                >
                    All
                </button>
                <button
                    type="button"
                    wire:click="$set('filterBilling', 'unbilled')"
                    class="px-3 py-1 rounded-lg transition-all {{ $filterBilling === 'unbilled' ? 'bg-violet-600 text-white font-medium shadow' : 'text-zinc-400 hover:text-zinc-200' }}"
                >
                    Unbilled
                </button>
                <button
                    type="button"
                    wire:click="$set('filterBilling', 'invoiced')"
                    class="px-3 py-1 rounded-lg transition-all {{ $filterBilling === 'invoiced' ? 'bg-violet-600 text-white font-medium shadow' : 'text-zinc-400 hover:text-zinc-200' }}"
                >
                    Invoiced
                </button>
            </div>
        </div>

        <!-- Search Input -->
        <div class="w-full md:w-72 relative">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search descriptions, git refs..."
                class="w-full bg-[#171A24] text-xs text-zinc-200 placeholder-zinc-500 border border-white/[0.08] rounded-xl pl-9 pr-3 py-2 focus:outline-none focus:border-violet-500 transition-colors"
            />
            <svg class="w-4 h-4 text-zinc-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
    </div>

    <!-- Timesheet Table -->
    <div class="rounded-2xl bg-[#12141C] border border-white/[0.07] overflow-hidden shadow-xl shadow-black/30">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#171A24]/60 border-b border-white/[0.07] text-zinc-400 uppercase font-semibold text-[10px] tracking-wider">
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Project</th>
                        <th class="py-3 px-4">Description / Git</th>
                        <th class="py-3 px-4 text-right">Duration</th>
                        <th class="py-3 px-4 text-right">Hourly Rate</th>
                        <th class="py-3 px-4 text-right">Billable Total</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($entries as $entry)
                        <tr class="hover:bg-white/[0.02] transition-colors {{ $entry->is_invoiced ? 'bg-zinc-900/20' : '' }}">
                            <td class="py-3.5 px-4 whitespace-nowrap text-zinc-400 font-mono text-[11px]" data-mono>
                                {{ $entry->started_at->format('Y-m-d') }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full flex-shrink-0" style="background-color: {{ $entry->project->color }}"></span>
                                    <span class="font-medium text-zinc-200">{{ $entry->project->name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 max-w-md">
                                <div class="text-zinc-200 font-medium truncate">{{ $entry->description }}</div>
                                @if($entry->git_ref)
                                    <div class="text-[10px] font-mono text-zinc-500 mt-0.5">git: {{ $entry->git_ref }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap font-mono font-medium {{ $entry->ended_at ? 'text-zinc-200' : 'text-emerald-400' }}" data-mono>
                                {{ $entry->formatted_duration }}
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap font-mono text-zinc-400" data-mono>
                                {{ $entry->project->client->currency ?? 'MYR' }} {{ number_format($entry->hourly_rate_snapshot, 2) }}/h
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap font-mono font-semibold {{ $entry->is_billable ? 'text-zinc-100' : 'text-zinc-600' }}" data-mono>
                                @if($entry->is_billable)
                                    {{ $entry->project->client->currency ?? 'MYR' }} {{ number_format($entry->billable_amount, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($entry->is_invoiced)
                                    <x-status-pill variant="amber" size="xs">
                                        🔒 {{ $entry->invoice->invoice_number ?? 'INVOICED' }}
                                    </x-status-pill>
                                @elseif($entry->ended_at === null)
                                    <x-status-pill variant="emerald" size="xs">
                                        ● Running
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
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($entry->ended_at !== null)
                                        <button
                                            type="button"
                                            wire:click="resumeEntry({{ $entry->id }})"
                                            title="Resume task in timer"
                                            class="p-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white transition-colors cursor-pointer"
                                        >
                                            <span class="font-mono text-xs">↻</span>
                                        </button>
                                    @endif

                                    @if(!$entry->is_invoiced)
                                        <button
                                            type="button"
                                            wire:click="deleteEntry({{ $entry->id }})"
                                            wire:confirm="Are you sure you want to delete this unbilled time entry?"
                                            title="Delete entry"
                                            class="p-1.5 rounded-lg bg-zinc-800 hover:bg-rose-950 text-zinc-400 hover:text-rose-400 transition-colors cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @else
                                        <span class="p-1.5 text-zinc-600 cursor-not-allowed" title="Locked by Invoice">
                                            🔒
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-zinc-500">
                                No time entries match current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($entries->hasPages())
            <div class="p-4 border-t border-white/[0.07]">
                {{ $entries->links() }}
            </div>
        @endif
    </div>

    <!-- Manual Time Entry Modal -->
    <div
        x-data="{ show: @entangle('showManualModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
    >
        <div
            @click.away="$wire.closeManualModal()"
            class="w-full max-w-lg bg-[#12141C] border border-white/[0.1] rounded-2xl p-6 shadow-2xl relative"
        >
            <div class="flex items-center justify-between pb-4 border-b border-white/[0.08]">
                <h3 class="text-base font-semibold text-white">Log Time Manually</h3>
                <button type="button" wire:click="closeManualModal" class="text-zinc-400 hover:text-white">✕</button>
            </div>

            @if($errorMessage)
                <div class="mt-4 p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-xs text-rose-300">
                    {{ $errorMessage }}
                </div>
            @endif

            <div class="space-y-4 mt-4">
                <div>
                    <label class="block text-xs font-medium text-zinc-400 mb-1.5">Project</label>
                    <select
                        wire:model="manualProjectId"
                        class="w-full bg-[#171A24] text-xs font-medium text-zinc-200 border border-white/[0.08] rounded-xl px-3 py-2.5 focus:outline-none focus:border-violet-500"
                    >
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-400 mb-1.5">Description</label>
                    <input
                        type="text"
                        wire:model="manualDescription"
                        placeholder="e.g. Profiling database queries on checkout"
                        class="w-full bg-[#171A24] text-xs text-zinc-200 border border-white/[0.08] rounded-xl px-3 py-2.5 focus:outline-none focus:border-violet-500"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-zinc-400 mb-1.5">
                            Duration <span class="text-zinc-500">(1h 30m, 45m, 1.5)</span>
                        </label>
                        <input
                            type="text"
                            wire:model="manualDuration"
                            placeholder="e.g. 1h 30m"
                            class="w-full bg-[#171A24] text-xs text-zinc-200 border border-white/[0.08] rounded-xl px-3 py-2.5 font-mono focus:outline-none focus:border-violet-500"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-400 mb-1.5">Date</label>
                        <input
                            type="date"
                            wire:model="manualDate"
                            class="w-full bg-[#171A24] text-xs text-zinc-200 border border-white/[0.08] rounded-xl px-3 py-2.5 focus:outline-none focus:border-violet-500"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 items-center">
                    <div>
                        <label class="block text-xs font-medium text-zinc-400 mb-1.5">Git Ref / Branch</label>
                        <input
                            type="text"
                            wire:model="manualGitRef"
                            placeholder="e.g. feat/db-index"
                            class="w-full bg-[#171A24] text-xs text-zinc-200 border border-white/[0.08] rounded-xl px-3 py-2.5 font-mono focus:outline-none focus:border-violet-500"
                        />
                    </div>

                    <div class="pt-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:model="manualIsBillable"
                                class="rounded border-zinc-700 bg-zinc-800 text-violet-600 focus:ring-0"
                            />
                            <span class="text-xs text-zinc-300">Billable to Client</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-white/[0.08]">
                <button
                    type="button"
                    wire:click="closeManualModal"
                    class="px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-xs font-medium text-zinc-300 transition-colors"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    wire:click="saveManualEntry"
                    class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-xs font-semibold text-white shadow-lg shadow-violet-600/20 transition-all cursor-pointer"
                >
                    Save Time Entry
                </button>
            </div>
        </div>
    </div>
</div>
