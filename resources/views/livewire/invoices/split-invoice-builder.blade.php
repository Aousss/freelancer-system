<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between pb-2 border-b border-white/[0.07]">
        <div>
            <div class="flex items-center gap-2 text-xs text-zinc-400">
                <a href="/invoices" class="hover:text-white">Invoices</a>
                <span>/</span>
                <span class="text-zinc-200">New Invoice</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white mt-1">Split-Screen Invoice Builder</h1>
        </div>

        <div class="flex items-center gap-2">
            <a href="/invoices" class="px-3.5 py-1.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-xs font-medium text-zinc-300 transition-colors">
                Cancel
            </a>
            <button
                type="button"
                wire:click="generateInvoice"
                class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-black text-xs font-bold shadow-lg shadow-emerald-500/20 active:scale-95 transition-all flex items-center gap-2 cursor-pointer"
            >
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19 12v7H5v-7H3v7a2 2 0 002 2h14a2 2 0 002-2v-7h-2zm-6 .67l2.59-2.58L17 11.5l-5 5-5-5 1.41-1.41L11 12.67V3h2v9.67z"/></svg>
                <span>Generate & Lock Invoice</span>
            </button>
        </div>
    </div>

    @if($errorMessage)
        <div class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-xs text-rose-300 flex items-center justify-between">
            <span>{{ $errorMessage }}</span>
            <button type="button" wire:click="$set('errorMessage', '')" class="text-rose-400 hover:text-rose-200">✕</button>
        </div>
    @endif

    <!-- Split Screen Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- LEFT PANE: Controls & Time Entry Checklist (5 cols) -->
        <div class="lg:col-span-5 space-y-5">
            <!-- Client & Project Selector -->
            <div class="rounded-2xl bg-[#12141C] border border-white/[0.07] p-5 shadow-lg shadow-black/30 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-400">1. Client & Project</h3>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-zinc-400 mb-1">Client</label>
                        <select
                            wire:model.live="clientId"
                            class="w-full bg-[#171A24] text-xs font-medium text-zinc-200 border border-white/[0.08] rounded-xl px-3 py-2.5 focus:outline-none focus:border-violet-500"
                        >
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->currency }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-400 mb-1">Project Scope (Optional)</label>
                        <select
                            wire:model.live="projectId"
                            class="w-full bg-[#171A24] text-xs font-medium text-zinc-200 border border-white/[0.08] rounded-xl px-3 py-2.5 focus:outline-none focus:border-violet-500"
                        >
                            <option value="">All Projects for Client</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Aggregation & Tax Mode -->
            <div class="rounded-2xl bg-[#12141C] border border-white/[0.07] p-5 shadow-lg shadow-black/30 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-400">2. Aggregation & Rates</h3>

                <div>
                    <label class="block text-xs font-medium text-zinc-400 mb-2">Line Item Grouping</label>
                    <div class="space-y-2">
                        <label class="flex items-start gap-2.5 p-2.5 rounded-xl bg-[#171A24] border border-white/[0.06] hover:border-violet-500/30 cursor-pointer transition-all">
                            <input
                                type="radio"
                                wire:model.live="aggregationMode"
                                value="detailed"
                                class="mt-0.5 text-violet-600 focus:ring-0"
                            />
                            <div>
                                <div class="text-xs font-semibold text-zinc-200">Detailed (1:1 with Time Logs)</div>
                                <div class="text-[11px] text-zinc-400">Each time entry appears as its own line with date & git commit.</div>
                            </div>
                        </label>

                        <label class="flex items-start gap-2.5 p-2.5 rounded-xl bg-[#171A24] border border-white/[0.06] hover:border-violet-500/30 cursor-pointer transition-all">
                            <input
                                type="radio"
                                wire:model.live="aggregationMode"
                                value="grouped_by_task"
                                class="mt-0.5 text-violet-600 focus:ring-0"
                            />
                            <div>
                                <div class="text-xs font-semibold text-zinc-200">Grouped by Task</div>
                                <div class="text-[11px] text-zinc-400">Sums durations under each parent task deliverable.</div>
                            </div>
                        </label>

                        <label class="flex items-start gap-2.5 p-2.5 rounded-xl bg-[#171A24] border border-white/[0.06] hover:border-violet-500/30 cursor-pointer transition-all">
                            <input
                                type="radio"
                                wire:model.live="aggregationMode"
                                value="single_line"
                                class="mt-0.5 text-violet-600 focus:ring-0"
                            />
                            <div>
                                <div class="text-xs font-semibold text-zinc-200">Single Summary Line</div>
                                <div class="text-[11px] text-zinc-400">Rolls all hours into a single consulting services line.</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div>
                        <label class="block text-xs font-medium text-zinc-400 mb-1">Tax Rate (%)</label>
                        <input
                            type="number"
                            step="0.5"
                            wire:model.live="taxRate"
                            placeholder="0.00"
                            class="w-full bg-[#171A24] text-xs font-mono text-zinc-200 border border-white/[0.08] rounded-xl px-3 py-2 focus:outline-none focus:border-violet-500"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-400 mb-1">Due Date</label>
                        <input
                            type="date"
                            wire:model.live="dueDate"
                            class="w-full bg-[#171A24] text-xs text-zinc-200 border border-white/[0.08] rounded-xl px-3 py-2 focus:outline-none focus:border-violet-500"
                        />
                    </div>
                </div>
            </div>

            <!-- Unbilled Time Entries Checklist -->
            <div class="rounded-2xl bg-[#12141C] border border-white/[0.07] p-5 shadow-lg shadow-black/30 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-white/[0.06]">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-400">3. Select Unbilled Logs</h3>
                        <p class="text-[11px] text-zinc-500">{{ count($selectedEntryIds) }} of {{ $eligibleEntries->count() }} selected</p>
                    </div>

                    <label class="flex items-center gap-1.5 text-xs text-zinc-400 cursor-pointer hover:text-zinc-200">
                        <input
                            type="checkbox"
                            wire:model.live="selectAll"
                            class="rounded border-zinc-700 bg-zinc-800 text-violet-600 focus:ring-0"
                        />
                        <span>Toggle All</span>
                    </label>
                </div>

                <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                    @forelse($eligibleEntries as $entry)
                        <label class="flex items-start gap-2.5 p-2 rounded-xl bg-[#171A24] border border-white/[0.04] hover:border-violet-500/20 cursor-pointer transition-all">
                            <input
                                type="checkbox"
                                wire:model.live="selectedEntryIds"
                                value="{{ $entry->id }}"
                                class="mt-1 rounded border-zinc-700 bg-zinc-800 text-violet-600 focus:ring-0"
                            />
                            <div class="flex-1 min-w-0 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-zinc-200 truncate">{{ $entry->description }}</span>
                                    <span class="font-mono font-semibold text-zinc-300 ml-2" data-mono>
                                        {{ $entry->formatted_hours }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-zinc-400 mt-0.5 font-mono">
                                    <span>{{ $entry->started_at->format('M d') }} · {{ $entry->project->name }}</span>
                                    <span>{{ $entry->project->client->currency }} {{ number_format($entry->billable_amount, 2) }}</span>
                                </div>
                            </div>
                        </label>
                    @empty
                        <div class="py-8 text-center text-zinc-500 text-xs">
                            No unbilled entries found for this client.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- RIGHT PANE: Live A4 Aspect Preview (7 cols) -->
        <div class="lg:col-span-7 sticky top-20">
            <div class="text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-2 flex items-center justify-between">
                <span>Real-Time A4 Print Preview</span>
                <span class="font-mono text-emerald-400">● LIVE SYNC</span>
            </div>

            <!-- Paper Sheet Simulation -->
            <div class="bg-white text-slate-800 rounded-2xl shadow-2xl border border-white/[0.1] p-8 md:p-10 font-sans text-xs">
                <!-- Header -->
                <div class="flex justify-between items-start pb-6 border-b border-slate-200">
                    <div>
                        <div class="text-xl font-bold tracking-tight text-slate-900">FreelanceOS</div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-widest mt-0.5">Software Engineering & Consulting</div>
                        <div class="text-[11px] text-slate-600 mt-2">Alex Rivers</div>
                        <div class="text-[11px] text-slate-500">developer@freelanceos.test</div>
                    </div>

                    <div class="text-right">
                        <div class="text-2xl font-black text-blue-600 tracking-tight">INVOICE</div>
                        <div class="font-mono font-semibold text-slate-800 mt-0.5">#INV-2026-PREVIEW</div>
                        <div class="mt-2 inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700">
                            DRAFT
                        </div>
                    </div>
                </div>

                <!-- Billed To & Metadata -->
                <div class="grid grid-cols-2 gap-6 py-6 border-b border-slate-200">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Billed To</div>
                        <div class="font-bold text-sm text-slate-900">{{ $preview['client']->name ?? 'Select Client' }}</div>
                        @if(!empty($preview['client']->contact_name))
                            <div class="text-slate-600 mt-0.5">Attn: {{ $preview['client']->contact_name }}</div>
                        @endif
                        @if(!empty($preview['client']->address))
                            <div class="text-slate-500 whitespace-pre-line mt-1">{{ $preview['client']->address }}</div>
                        @endif
                    </div>

                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 space-y-1 font-mono text-[11px]">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Issue Date:</span>
                            <span class="font-medium text-slate-800">{{ now()->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Due Date:</span>
                            <span class="font-medium text-slate-800">{{ $dueDate ? \Carbon\Carbon::parse($dueDate)->format('M d, Y') : '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Currency:</span>
                            <span class="font-bold text-slate-900">{{ $preview['currency'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Live Generated Items Table -->
                <div class="py-6">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px] tracking-wider border-y border-slate-200">
                                <th class="py-2.5 px-3">Description</th>
                                <th class="py-2.5 px-3 text-right">Hours</th>
                                <th class="py-2.5 px-3 text-right">Rate</th>
                                <th class="py-2.5 px-3 text-right">Amount ({{ $preview['currency'] }})</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($preview['items'] as $item)
                                <tr>
                                    <td class="py-2.5 px-3 font-medium text-slate-800">{{ $item['description'] }}</td>
                                    <td class="py-2.5 px-3 text-right font-mono text-slate-600">{{ number_format($item['quantity_hours'], 2) }}</td>
                                    <td class="py-2.5 px-3 text-right font-mono text-slate-600">{{ number_format($item['unit_rate'], 2) }}</td>
                                    <td class="py-2.5 px-3 text-right font-mono font-semibold text-slate-900">{{ number_format($item['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400 italic">
                                        No unbilled time entries selected. Check boxes on the left to add line items.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Totals Section -->
                <div class="pt-4 border-t border-slate-200 flex justify-end">
                    <div class="w-64 space-y-1.5 font-mono text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal:</span>
                            <span>{{ $preview['currency'] }} {{ number_format($preview['subtotal'], 2) }}</span>
                        </div>
                        @if((float) $taxRate > 0)
                            <div class="flex justify-between text-slate-600">
                                <span>Tax ({{ number_format($taxRate, 1) }}%):</span>
                                <span>{{ $preview['currency'] }} {{ number_format($preview['taxAmount'], 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between pt-2 border-t-2 border-slate-900 text-sm font-bold text-slate-900">
                            <span>Total Due:</span>
                            <span class="text-blue-600">{{ $preview['currency'] }} {{ number_format($preview['total'], 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                @if($notes)
                    <div class="mt-8 pt-4 border-t border-slate-200 text-[11px] text-slate-500">
                        <div class="font-bold uppercase tracking-wider text-slate-400 text-[10px] mb-1">Notes & Terms</div>
                        <div class="whitespace-pre-line">{{ $notes }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
