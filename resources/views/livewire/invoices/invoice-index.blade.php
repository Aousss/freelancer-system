<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                Invoices & Billing
            </h1>
            <p class="text-xs text-zinc-400 mt-0.5">Automated PDF generation, unbilled time aggregation, and payment tracking.</p>
        </div>

        <div class="flex items-center gap-2.5 self-start sm:self-auto flex-wrap">
            <button
                type="button"
                wire:click="openHistoricalModal"
                class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-semibold shadow-lg shadow-amber-500/10 active:scale-95 transition-all"
            >
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Add Historical / Paid Invoice</span>
            </button>

            <a
                href="/invoices/new"
                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-semibold shadow-lg shadow-violet-600/20 active:scale-95 transition-all"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Split-Screen Invoice Builder</span>
            </a>
        </div>
    </div>

    <!-- Feedback messages -->
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

    <!-- Status Filter Tabs -->
    <div class="flex items-center gap-2 bg-[#12141C] border border-white/[0.07] p-1.5 rounded-2xl w-fit text-xs">
        <button
            type="button"
            wire:click="$set('statusFilter', 'all')"
            class="px-3 py-1.5 rounded-xl transition-all {{ $statusFilter === 'all' ? 'bg-violet-600 text-white font-medium shadow' : 'text-zinc-400 hover:text-zinc-200' }}"
        >
            All
        </button>
        <button
            type="button"
            wire:click="$set('statusFilter', 'draft')"
            class="px-3 py-1.5 rounded-xl transition-all {{ $statusFilter === 'draft' ? 'bg-violet-600 text-white font-medium shadow' : 'text-zinc-400 hover:text-zinc-200' }}"
        >
            Draft
        </button>
        <button
            type="button"
            wire:click="$set('statusFilter', 'sent')"
            class="px-3 py-1.5 rounded-xl transition-all {{ $statusFilter === 'sent' ? 'bg-violet-600 text-white font-medium shadow' : 'text-zinc-400 hover:text-zinc-200' }}"
        >
            Sent
        </button>
        <button
            type="button"
            wire:click="$set('statusFilter', 'paid')"
            class="px-3 py-1.5 rounded-xl transition-all {{ $statusFilter === 'paid' ? 'bg-violet-600 text-white font-medium shadow' : 'text-zinc-400 hover:text-zinc-200' }}"
        >
            Paid
        </button>
        <button
            type="button"
            wire:click="$set('statusFilter', 'void')"
            class="px-3 py-1.5 rounded-xl transition-all {{ $statusFilter === 'void' ? 'bg-violet-600 text-white font-medium shadow' : 'text-zinc-400 hover:text-zinc-200' }}"
        >
            Void
        </button>
    </div>

    <!-- Invoices Table -->
    <div class="rounded-2xl bg-[#12141C] border border-white/[0.07] overflow-hidden shadow-xl shadow-black/30">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#171A24]/60 border-b border-white/[0.07] text-zinc-400 uppercase font-semibold text-[10px] tracking-wider">
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-4">Client</th>
                        <th class="py-3 px-4">Project</th>
                        <th class="py-3 px-4">Issue Date</th>
                        <th class="py-3 px-4">Due Date</th>
                        <th class="py-3 px-4 text-right">Total</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="py-3.5 px-4 whitespace-nowrap font-mono font-bold text-zinc-100" data-mono>
                                <a href="/invoices/{{ $inv->id }}/pdf" target="_blank" class="hover:text-violet-400 hover:underline">
                                    {{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-zinc-200 font-medium">
                                {{ $inv->client->name }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-zinc-400">
                                {{ $inv->project->name ?? 'Multiple / General' }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-zinc-400 font-mono text-[11px]" data-mono>
                                {{ $inv->issue_date->format('M d, Y') }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-zinc-400 font-mono text-[11px]" data-mono>
                                {{ $inv->due_date->format('M d, Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap font-mono font-bold text-zinc-100" data-mono>
                                {{ $inv->currency }} {{ number_format($inv->total, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $inv->status->badgeClass() }}">
                                    {{ $inv->status->label() }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- View PDF in new tab -->
                                    <a
                                        href="/invoices/{{ $inv->id }}/pdf"
                                        target="_blank"
                                        title="View rendered PDF"
                                        class="p-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>

                                    <!-- Download PDF file -->
                                    <a
                                        href="/invoices/{{ $inv->id }}/pdf?download=1"
                                        title="Download A4 PDF"
                                        class="p-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>

                                    @if($inv->status->value !== 'paid')
                                        <button
                                            type="button"
                                            wire:click="markAsPaid({{ $inv->id }})"
                                            title="Mark as Paid"
                                            class="p-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    @endif

                                    @if($inv->status->value !== 'paid' && $inv->status->value !== 'void')
                                        <button
                                            type="button"
                                            wire:click="voidInvoice({{ $inv->id }})"
                                            wire:confirm="Void this invoice? This will unlock all linked time entries so they can be re-billed."
                                            title="Void and unlock entries"
                                            class="p-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    @endif

                                    <!-- Delete invoice button -->
                                    <button
                                        type="button"
                                        wire:click="deleteInvoice({{ $inv->id }})"
                                        wire:confirm="Permanently delete invoice {{ $inv->invoice_number }}? This cannot be undone."
                                        title="Delete invoice"
                                        class="p-1.5 rounded-lg bg-zinc-800 hover:bg-rose-500/20 text-zinc-400 hover:text-rose-400 border border-white/[0.05] transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-zinc-500">
                                No invoices found. Click "Add Historical / Paid Invoice" to record previous invoices, or "Split-Screen Invoice Builder" to bill unbilled hours.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-4 border-t border-white/[0.07]">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

    <!-- ========================================== -->
    <!-- ADD HISTORICAL / PAID INVOICE MODAL        -->
    <!-- ========================================== -->
    @if($showHistoricalModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm overflow-y-auto" role="dialog" aria-modal="true">
        <div class="relative w-full max-w-2xl my-8 rounded-3xl bg-[#12141C] border border-white/10 p-6 sm:p-8 shadow-2xl space-y-6">
            <!-- Modal Header -->
            <div class="flex items-start justify-between gap-4 pb-4 border-b border-white/[0.07]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white tracking-tight">Record Historical / Paid Invoice</h2>
                        <p class="text-xs text-zinc-400">Add invoices issued prior to FreelanceOS. Next invoice numbers will continue sequentially from this.</p>
                    </div>
                </div>
                <button
                    type="button"
                    wire:click="closeHistoricalModal"
                    class="p-2 rounded-xl text-zinc-400 hover:text-white hover:bg-white/[0.05] transition-colors"
                >
                    ✕
                </button>
            </div>

            <!-- Error banner -->
            @if($errorMessage)
                <div class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-xs text-rose-300">
                    {{ $errorMessage }}
                </div>
            @endif

            <form wire:submit="saveHistoricalInvoice" class="space-y-5">
                <!-- Top Row: Client & Project -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Client <span class="text-rose-400">*</span></label>
                        <select
                            wire:model.live="historicalClientId"
                            class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-amber-500"
                        >
                            <option value="">Select a client...</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->currency }})</option>
                            @endforeach
                        </select>
                        @error('historicalClientId') <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Project (Optional)</label>
                        <select
                            wire:model="historicalProjectId"
                            class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-amber-500"
                        >
                            <option value="">None / General Billing</option>
                            @foreach($historicalProjects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        @error('historicalProjectId') <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Invoice Number & Currency -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">
                            Invoice Number <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                wire:model="historicalInvoiceNumber"
                                placeholder="e.g. INV-2026-001"
                                class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white font-mono focus:outline-none focus:border-amber-500"
                            />
                        </div>
                        <p class="text-[11px] text-zinc-400 mt-1">
                            Set your previous invoice number. Future invoices will automatically continue consecutively from this sequence!
                        </p>
                        @error('historicalInvoiceNumber') <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Currency <span class="text-rose-400">*</span></label>
                        <input
                            type="text"
                            wire:model="historicalCurrency"
                            placeholder="MYR"
                            maxlength="3"
                            class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white uppercase font-mono focus:outline-none focus:border-amber-500"
                        />
                        @error('historicalCurrency') <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Dates & Status -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Issue Date <span class="text-rose-400">*</span></label>
                        <input
                            type="date"
                            wire:model="historicalIssueDate"
                            class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-500"
                        />
                        @error('historicalIssueDate') <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Due Date <span class="text-rose-400">*</span></label>
                        <input
                            type="date"
                            wire:model="historicalDueDate"
                            class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-500"
                        />
                        @error('historicalDueDate') <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Status <span class="text-rose-400">*</span></label>
                        <select
                            wire:model.live="historicalStatus"
                            class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-500"
                        >
                            <option value="paid">Paid (Settled)</option>
                            <option value="sent">Sent (Unpaid)</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Paid At</label>
                        <input
                            type="date"
                            wire:model="historicalPaidAt"
                            class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-500 {{ $historicalStatus !== 'paid' ? 'opacity-40 cursor-not-allowed' : '' }}"
                            {{ $historicalStatus !== 'paid' ? 'disabled' : '' }}
                        />
                    </div>
                </div>

                <!-- Line Items Section -->
                <div class="space-y-3 pt-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-semibold text-zinc-200 uppercase tracking-wider">Line Items <span class="text-rose-400">*</span></label>
                        <button
                            type="button"
                            wire:click="addHistoricalItem"
                            class="flex items-center gap-1.5 text-xs text-amber-400 hover:text-amber-300 font-medium transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Add Item</span>
                        </button>
                    </div>

                    <div class="space-y-2.5">
                        @foreach($historicalItems as $index => $item)
                            <div class="p-3 rounded-2xl bg-[#171A24]/70 border border-white/[0.06] flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                <div class="flex-1 w-full sm:w-auto">
                                    <input
                                        type="text"
                                        wire:model.live="historicalItems.{{ $index }}.description"
                                        placeholder="Service description (e.g., Full Stack Development, Consulting Retainer)"
                                        class="w-full bg-black/20 border border-white/[0.08] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-500"
                                    />
                                    @error("historicalItems.{$index}.description") <p class="text-rose-400 text-[10px] mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="w-24">
                                    <input
                                        type="number"
                                        step="0.01"
                                        wire:model.live="historicalItems.{{ $index }}.quantity_hours"
                                        placeholder="Qty/Hrs"
                                        class="w-full bg-black/20 border border-white/[0.08] rounded-xl px-2.5 py-2 text-xs text-white font-mono text-right focus:outline-none focus:border-amber-500"
                                    />
                                    @error("historicalItems.{$index}.quantity_hours") <p class="text-rose-400 text-[10px] mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="w-28">
                                    <input
                                        type="number"
                                        step="0.01"
                                        wire:model.live="historicalItems.{{ $index }}.unit_rate"
                                        placeholder="Rate/Price"
                                        class="w-full bg-black/20 border border-white/[0.08] rounded-xl px-2.5 py-2 text-xs text-white font-mono text-right focus:outline-none focus:border-amber-500"
                                    />
                                    @error("historicalItems.{$index}.unit_rate") <p class="text-rose-400 text-[10px] mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="w-24 text-right font-mono text-xs font-bold text-zinc-100 whitespace-nowrap">
                                    {{ $historicalCurrency }} {{ number_format(((float)($item['quantity_hours'] ?? 0)) * ((float)($item['unit_rate'] ?? 0)), 2) }}
                                </div>

                                @if(count($historicalItems) > 1)
                                    <button
                                        type="button"
                                        wire:click="removeHistoricalItem({{ $index }})"
                                        class="text-zinc-500 hover:text-rose-400 p-1 transition-colors"
                                        title="Remove item"
                                    >
                                        ✕
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Tax Rate & Live Financial Summary -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end pt-2">
                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Tax Rate (%)</label>
                        <input
                            type="number"
                            step="0.1"
                            min="0"
                            max="100"
                            wire:model.live="historicalTaxRate"
                            placeholder="0.0"
                            class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2 text-xs text-white font-mono focus:outline-none focus:border-amber-500"
                        />
                    </div>

                    <!-- Live Summary Box -->
                    <div class="p-3.5 rounded-2xl bg-white/[0.02] border border-white/[0.07] space-y-1.5 text-xs font-mono">
                        <div class="flex items-center justify-between text-zinc-400">
                            <span>Subtotal:</span>
                            <span>{{ $historicalCurrency }} {{ number_format($this->historicalSubtotal, 2) }}</span>
                        </div>
                        @if((float) $historicalTaxRate > 0)
                            <div class="flex items-center justify-between text-zinc-400">
                                <span>Tax ({{ $historicalTaxRate }}%):</span>
                                <span>{{ $historicalCurrency }} {{ number_format($this->historicalTaxAmount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between text-white font-bold pt-1 border-t border-white/[0.08]">
                            <span>Total Amount:</span>
                            <span class="text-amber-400">{{ $historicalCurrency }} {{ number_format($this->historicalTotal, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-xs font-medium text-zinc-300 mb-1.5">Notes / Remittance Record</label>
                    <textarea
                        wire:model="historicalNotes"
                        rows="2"
                        placeholder="e.g. Paid in full via bank transfer on 2026-08-15."
                        class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-amber-500"
                    ></textarea>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/[0.07]">
                    <button
                        type="button"
                        wire:click="closeHistoricalModal"
                        class="px-4 py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-xs font-medium transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-black text-xs font-bold shadow-lg shadow-amber-500/20 active:scale-95 transition-all flex items-center gap-2"
                    >
                        <span wire:loading.remove wire:target="saveHistoricalInvoice">Save & Record Invoice</span>
                        <span wire:loading wire:target="saveHistoricalInvoice">Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
