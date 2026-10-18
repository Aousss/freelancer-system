<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-violet-600/20 border border-violet-500/30 flex items-center justify-center text-violet-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <span>System & Billing Settings</span>
            </h1>
            <p class="text-xs text-zinc-400 mt-1">Configure your default currency, invoice sequence numbers, studio branding, and payment remittance.</p>
        </div>

        <button
            type="button"
            wire:click="saveSettings"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-semibold shadow-lg shadow-violet-600/25 active:scale-95 transition-all self-start sm:self-auto"
        >
            <span wire:loading.remove wire:target="saveSettings">Save All Settings</span>
            <span wire:loading wire:target="saveSettings">Saving Changes...</span>
        </button>
    </div>

    <!-- Feedback banner -->
    @if($successMessage)
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-xs text-emerald-300 flex items-center justify-between shadow-lg shadow-emerald-500/5">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ $successMessage }}</span>
            </div>
            <button type="button" wire:click="$set('successMessage', '')" class="text-emerald-400 hover:text-emerald-200">✕</button>
        </div>
    @endif

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-1.5 bg-[#12141C] border border-white/[0.07] p-1.5 rounded-2xl w-fit text-xs overflow-x-auto max-w-full">
        <button
            type="button"
            wire:click="$set('activeTab', 'currency')"
            class="flex items-center gap-2 px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ $activeTab === 'currency' ? 'bg-violet-600 text-white font-semibold shadow-md shadow-violet-600/25' : 'text-zinc-400 hover:text-zinc-200 hover:bg-white/[0.03]' }}"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Currency & Financials</span>
        </button>

        <button
            type="button"
            wire:click="$set('activeTab', 'invoicing')"
            class="flex items-center gap-2 px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ $activeTab === 'invoicing' ? 'bg-violet-600 text-white font-semibold shadow-md shadow-violet-600/25' : 'text-zinc-400 hover:text-zinc-200 hover:bg-white/[0.03]' }}"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
            <span>Invoice Numbering & Rules</span>
        </button>

        <button
            type="button"
            wire:click="$set('activeTab', 'profile')"
            class="flex items-center gap-2 px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ $activeTab === 'profile' ? 'bg-violet-600 text-white font-semibold shadow-md shadow-violet-600/25' : 'text-zinc-400 hover:text-zinc-200 hover:bg-white/[0.03]' }}"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span>Studio / Business Profile</span>
        </button>

        <button
            type="button"
            wire:click="$set('activeTab', 'banking')"
            class="flex items-center gap-2 px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ $activeTab === 'banking' ? 'bg-violet-600 text-white font-semibold shadow-md shadow-violet-600/25' : 'text-zinc-400 hover:text-zinc-200 hover:bg-white/[0.03]' }}"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            <span>Bank & Remittance</span>
        </button>
    </div>

    <!-- Tab 1: Currency & Financials -->
    @if($activeTab === 'currency')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-3xl bg-[#12141C] border border-white/[0.07] p-6 sm:p-7 shadow-xl shadow-black/30 space-y-5">
                <div>
                    <h2 class="text-base font-bold text-white tracking-tight">Primary Currency & Formatting</h2>
                    <p class="text-xs text-zinc-400 mt-0.5">Select your default operating currency. Invoices, dashboard statistics, and rates will reflect this.</p>
                </div>

                <!-- Quick Selection Currency Cards -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-300 uppercase tracking-wider mb-2.5">Quick Pick Currency</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <button
                            type="button"
                            wire:click="selectQuickCurrency('MYR', 'RM')"
                            class="p-3 rounded-2xl border text-left transition-all {{ $currency === 'MYR' ? 'bg-violet-600/15 border-violet-500 text-white shadow-lg shadow-violet-600/10' : 'bg-[#171A24]/60 border-white/[0.06] text-zinc-400 hover:text-white hover:bg-white/[0.04]' }}"
                        >
                            <div class="font-bold text-sm text-white font-mono">MYR (RM)</div>
                            <div class="text-[11px] text-zinc-400 mt-0.5">Ringgit Malaysia</div>
                        </button>

                        <button
                            type="button"
                            wire:click="selectQuickCurrency('USD', '$')"
                            class="p-3 rounded-2xl border text-left transition-all {{ $currency === 'USD' ? 'bg-violet-600/15 border-violet-500 text-white shadow-lg shadow-violet-600/10' : 'bg-[#171A24]/60 border-white/[0.06] text-zinc-400 hover:text-white hover:bg-white/[0.04]' }}"
                        >
                            <div class="font-bold text-sm text-white font-mono">USD ($)</div>
                            <div class="text-[11px] text-zinc-400 mt-0.5">US Dollar</div>
                        </button>

                        <button
                            type="button"
                            wire:click="selectQuickCurrency('EUR', '€')"
                            class="p-3 rounded-2xl border text-left transition-all {{ $currency === 'EUR' ? 'bg-violet-600/15 border-violet-500 text-white shadow-lg shadow-violet-600/10' : 'bg-[#171A24]/60 border-white/[0.06] text-zinc-400 hover:text-white hover:bg-white/[0.04]' }}"
                        >
                            <div class="font-bold text-sm text-white font-mono">EUR (€)</div>
                            <div class="text-[11px] text-zinc-400 mt-0.5">Euro</div>
                        </button>

                        <button
                            type="button"
                            wire:click="selectQuickCurrency('GBP', '£')"
                            class="p-3 rounded-2xl border text-left transition-all {{ $currency === 'GBP' ? 'bg-violet-600/15 border-violet-500 text-white shadow-lg shadow-violet-600/10' : 'bg-[#171A24]/60 border-white/[0.06] text-zinc-400 hover:text-white hover:bg-white/[0.04]' }}"
                        >
                            <div class="font-bold text-sm text-white font-mono">GBP (£)</div>
                            <div class="text-[11px] text-zinc-400 mt-0.5">British Pound</div>
                        </button>

                        <button
                            type="button"
                            wire:click="selectQuickCurrency('SGD', 'S$')"
                            class="p-3 rounded-2xl border text-left transition-all {{ $currency === 'SGD' ? 'bg-violet-600/15 border-violet-500 text-white shadow-lg shadow-violet-600/10' : 'bg-[#171A24]/60 border-white/[0.06] text-zinc-400 hover:text-white hover:bg-white/[0.04]' }}"
                        >
                            <div class="font-bold text-sm text-white font-mono">SGD (S$)</div>
                            <div class="text-[11px] text-zinc-400 mt-0.5">Singapore Dollar</div>
                        </button>

                        <button
                            type="button"
                            wire:click="selectQuickCurrency('AUD', 'A$')"
                            class="p-3 rounded-2xl border text-left transition-all {{ $currency === 'AUD' ? 'bg-violet-600/15 border-violet-500 text-white shadow-lg shadow-violet-600/10' : 'bg-[#171A24]/60 border-white/[0.06] text-zinc-400 hover:text-white hover:bg-white/[0.04]' }}"
                        >
                            <div class="font-bold text-sm text-white font-mono">AUD (A$)</div>
                            <div class="text-[11px] text-zinc-400 mt-0.5">Australian Dollar</div>
                        </button>

                        <button
                            type="button"
                            wire:click="selectQuickCurrency('CAD', 'CA$')"
                            class="p-3 rounded-2xl border text-left transition-all {{ $currency === 'CAD' ? 'bg-violet-600/15 border-violet-500 text-white shadow-lg shadow-violet-600/10' : 'bg-[#171A24]/60 border-white/[0.06] text-zinc-400 hover:text-white hover:bg-white/[0.04]' }}"
                        >
                            <div class="font-bold text-sm text-white font-mono">CAD (CA$)</div>
                            <div class="text-[11px] text-zinc-400 mt-0.5">Canadian Dollar</div>
                        </button>

                        <button
                            type="button"
                            wire:click="selectQuickCurrency('JPY', '¥')"
                            class="p-3 rounded-2xl border text-left transition-all {{ $currency === 'JPY' ? 'bg-violet-600/15 border-violet-500 text-white shadow-lg shadow-violet-600/10' : 'bg-[#171A24]/60 border-white/[0.06] text-zinc-400 hover:text-white hover:bg-white/[0.04]' }}"
                        >
                            <div class="font-bold text-sm text-white font-mono">JPY (¥)</div>
                            <div class="text-[11px] text-zinc-400 mt-0.5">Japanese Yen</div>
                        </button>
                    </div>
                </div>

                <!-- Custom Inputs -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Currency ISO Code <span class="text-rose-400">*</span></label>
                        <input
                            type="text"
                            wire:model.live="currency"
                            maxlength="3"
                            placeholder="MYR"
                            class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white uppercase font-mono focus:outline-none focus:border-violet-500"
                        />
                        @error('currency') <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Currency Symbol <span class="text-rose-400">*</span></label>
                        <input
                            type="text"
                            wire:model.live="currencySymbol"
                            placeholder="RM"
                            class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white font-mono focus:outline-none focus:border-violet-500"
                        />
                        @error('currencySymbol') <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Symbol Position</label>
                        <select
                            wire:model.live="currencyPosition"
                            class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-violet-500"
                        >
                            <option value="prefix">Before Amount (e.g. {{ $currencySymbol }} 1,500.00)</option>
                            <option value="suffix">After Amount (e.g. 1,500.00 {{ $currency }})</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-white/[0.06]">
                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Default Tax / SST Rate (%)</label>
                        <input
                            type="number"
                            step="0.1"
                            min="0"
                            max="100"
                            wire:model="defaultTaxRate"
                            placeholder="0.0"
                            class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white font-mono focus:outline-none focus:border-violet-500"
                        />
                        <p class="text-[11px] text-zinc-400 mt-1">Applied automatically when generating new invoices.</p>
                        @error('defaultTaxRate') <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Default Hourly Rate ({{ $currency }})</label>
                        <input
                            type="number"
                            step="1.0"
                            min="0"
                            wire:model="defaultHourlyRate"
                            placeholder="150.00"
                            class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white font-mono focus:outline-none focus:border-violet-500"
                        />
                        <p class="text-[11px] text-zinc-400 mt-1">Fallback rate when client or project does not specify an override.</p>
                        @error('defaultHourlyRate') <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Live Visual Preview -->
        <div class="space-y-4">
            <div class="rounded-3xl bg-gradient-to-b from-[#171A24] to-[#12141C] border border-white/[0.09] p-6 shadow-xl space-y-4">
                <div class="flex items-center gap-2 text-xs font-semibold text-violet-400 uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Live Financial Display</span>
                </div>

                <div class="p-4 rounded-2xl bg-black/30 border border-white/[0.05] space-y-3 font-mono">
                    <div class="text-xs text-zinc-400">Sample Invoiced Amount:</div>
                    <div class="text-2xl font-bold text-white tracking-tight">
                        @if($currencyPosition === 'prefix')
                            {{ $currencySymbol }} 4,850.00
                        @else
                            4,850.00 {{ $currency }}
                        @endif
                    </div>
                    <div class="text-[11px] text-emerald-400 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>Active currency formatting</span>
                    </div>
                </div>

                <div class="text-xs text-zinc-400 space-y-2 pt-1 border-t border-white/[0.06]">
                    <div class="flex justify-between">
                        <span>Base Hourly Rate:</span>
                        <span class="text-white font-mono">{{ $currency }} {{ number_format($defaultHourlyRate, 2) }}/h</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Standard Tax:</span>
                        <span class="text-white font-mono">{{ number_format($defaultTaxRate, 1) }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Tab 2: Invoicing & Sequencing -->
    @if($activeTab === 'invoicing')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-3xl bg-[#12141C] border border-white/[0.07] p-6 sm:p-7 shadow-xl shadow-black/30 space-y-5">
                <div>
                    <h2 class="text-base font-bold text-white tracking-tight">Invoice Sequencing & Payment Rules</h2>
                    <p class="text-xs text-zinc-400 mt-0.5">Control prefix formats and due date terms for newly generated and historical invoices.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Invoice Prefix Format <span class="text-rose-400">*</span></label>
                        <input
                            type="text"
                            wire:model="invoicePrefix"
                            placeholder="INV-"
                            class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white font-mono focus:outline-none focus:border-violet-500"
                        />
                        <p class="text-[11px] text-zinc-400 mt-1">E.g. <code class="text-violet-300 font-mono">INV-</code> will generate <code class="text-zinc-200 font-mono">INV-2026-001</code>.</p>
                        @error('invoicePrefix') <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Default Payment Term (Days) <span class="text-rose-400">*</span></label>
                        <input
                            type="number"
                            min="1"
                            max="180"
                            wire:model="invoiceDueDays"
                            placeholder="14"
                            class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white font-mono focus:outline-none focus:border-violet-500"
                        />
                        <p class="text-[11px] text-zinc-400 mt-1">Sets the default Due Date (e.g. Net 14 or Net 30).</p>
                        @error('invoiceDueDays') <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-300 mb-1.5">Default Remittance Notes & Payment Instructions</label>
                    <textarea
                        wire:model="invoiceNotesTemplate"
                        rows="3"
                        class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-violet-500"
                        placeholder="Payment due within 14 days of issue date..."
                    ></textarea>
                    <p class="text-[11px] text-zinc-400 mt-1">Printed at the bottom of generated PDF invoices for clients.</p>
                </div>
            </div>
        </div>

        <!-- Right Side: Sequencing Status Card -->
        <div class="space-y-4">
            <div class="rounded-3xl bg-gradient-to-b from-[#171A24] to-[#12141C] border border-white/[0.09] p-6 shadow-xl space-y-4">
                <div class="flex items-center gap-2 text-xs font-semibold text-amber-400 uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Next Sequential Number</span>
                </div>

                <div class="p-4 rounded-2xl bg-black/30 border border-white/[0.05] space-y-2 font-mono">
                    <div class="text-xs text-zinc-400">Next Generated Invoice:</div>
                    <div class="text-xl font-bold text-amber-300 tracking-tight">
                        {{ $nextCalculatedInvoiceNumber }}
                    </div>
                    <div class="text-[11px] text-zinc-400 pt-1">
                        Computed consecutively from your highest recorded invoice.
                    </div>
                </div>

                <div class="text-xs text-zinc-400 space-y-2 pt-1 border-t border-white/[0.06]">
                    <p class="leading-relaxed">
                        To continue from an existing series, simply record your last past invoice via the Invoices page, or the system will automatically count upwards.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Tab 3: Business Profile & PDF Branding -->
    @if($activeTab === 'profile')
    <div class="max-w-3xl space-y-6">
        <div class="rounded-3xl bg-[#12141C] border border-white/[0.07] p-6 sm:p-7 shadow-xl shadow-black/30 space-y-5">
            <div>
                <h2 class="text-base font-bold text-white tracking-tight">Freelancer & Studio Branding</h2>
                <p class="text-xs text-zinc-400 mt-0.5">Your official business details printed on invoices and correspondence.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-zinc-300 mb-1.5">Freelancer Full Name <span class="text-rose-400">*</span></label>
                    <input
                        type="text"
                        wire:model="freelancerName"
                        placeholder="Alex Rivers"
                        class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-violet-500"
                    />
                    @error('freelancerName') <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-300 mb-1.5">Studio / Company Name <span class="text-rose-400">*</span></label>
                    <input
                        type="text"
                        wire:model="businessName"
                        placeholder="Alex Rivers Studio"
                        class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-violet-500"
                    />
                    @error('businessName') <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-zinc-300 mb-1.5">Subtitle / Tagline</label>
                    <input
                        type="text"
                        wire:model="businessSubtitle"
                        placeholder="Software Engineering & Consulting"
                        class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-violet-500"
                    />
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-300 mb-1.5">Contact Email <span class="text-rose-400">*</span></label>
                    <input
                        type="email"
                        wire:model="businessEmail"
                        placeholder="alex@rivers.dev"
                        class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-violet-500"
                    />
                    @error('businessEmail') <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-300 mb-1.5">Phone Number</label>
                    <input
                        type="text"
                        wire:model="businessPhone"
                        placeholder="+60 12-345 6789"
                        class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-violet-500"
                    />
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-zinc-300 mb-1.5">Business Registration / Tax ID Number</label>
                <input
                    type="text"
                    wire:model="businessRegistration"
                    placeholder="SSM: 202601002345 (003145678-X) / Tax ID"
                    class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-violet-500"
                />
                <p class="text-[11px] text-zinc-400 mt-1">Appears on official A4 tax invoices.</p>
            </div>

            <div>
                <label class="block text-xs font-medium text-zinc-300 mb-1.5">Business Mailing Address</label>
                <textarea
                    wire:model="businessAddress"
                    rows="3"
                    class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-violet-500"
                    placeholder="Level 28, Menara Ilham&#10;50450 Kuala Lumpur, Malaysia"
                ></textarea>
            </div>
        </div>
    </div>
    @endif

    <!-- Tab 4: Bank Account & Payment Remittance -->
    @if($activeTab === 'banking')
    <div class="max-w-3xl space-y-6">
        <div class="rounded-3xl bg-[#12141C] border border-white/[0.07] p-6 sm:p-7 shadow-xl shadow-black/30 space-y-5">
            <div>
                <h2 class="text-base font-bold text-white tracking-tight">Direct Bank & Wire Remittance</h2>
                <p class="text-xs text-zinc-400 mt-0.5">Your banking details provided to clients for wire transfers, DuitNow, and remittance.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-zinc-300 mb-1.5">Bank Name</label>
                    <input
                        type="text"
                        wire:model="bankName"
                        placeholder="Maybank (Malayan Banking Berhad)"
                        class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-violet-500"
                    />
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-300 mb-1.5">Account Holder Name</label>
                    <input
                        type="text"
                        wire:model="bankAccountName"
                        placeholder="Alex Rivers"
                        class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-violet-500"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-zinc-300 mb-1.5">Account Number</label>
                    <input
                        type="text"
                        wire:model="bankAccountNumber"
                        placeholder="5140 1234 5678"
                        class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white font-mono focus:outline-none focus:border-violet-500"
                    />
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-300 mb-1.5">SWIFT Code / DuitNow ID / IBAN</label>
                    <input
                        type="text"
                        wire:model="bankSwiftDuitnow"
                        placeholder="DuitNow ID: 900101-14-5555 / MBBEMYKL"
                        class="w-full bg-[#171A24] border border-white/[0.09] rounded-xl px-3.5 py-2.5 text-xs text-white font-mono focus:outline-none focus:border-violet-500"
                    />
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-violet-500/10 border border-violet-500/20 text-xs text-violet-300">
                <span class="font-semibold text-white">Pro-tip:</span> These banking details will be automatically included in your generated PDF invoices so clients can pay you instantly via bank transfer or local QR/DuitNow.
            </div>
        </div>
    </div>
    @endif
</div>
