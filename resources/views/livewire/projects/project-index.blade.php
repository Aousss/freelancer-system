<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                Projects & Workspaces
            </h1>
            <p class="text-xs text-zinc-400 mt-0.5">Scoping, client association, flexible rate models (Per Hour, Per Task, Per Project, Monthly), and Kanban boards.</p>
        </div>

        <button
            type="button"
            wire:click="openCreateModal"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-semibold shadow-lg shadow-violet-600/20 active:scale-95 transition-all self-start sm:self-auto cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <span>New Project</span>
        </button>
    </div>

    <!-- Flexible Rate Reference Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="p-3 rounded-xl bg-[#12141C] border border-violet-500/20 text-xs">
            <div class="text-[10px] font-mono font-bold text-violet-400 uppercase">1. Per Hour</div>
            <div class="text-zinc-200 font-semibold mt-0.5">Hourly Logging</div>
            <div class="text-[10px] text-zinc-400 mt-0.5">Hours × Hourly Rate</div>
        </div>
        <div class="p-3 rounded-xl bg-[#12141C] border border-blue-500/20 text-xs">
            <div class="text-[10px] font-mono font-bold text-blue-400 uppercase">2. Per Task</div>
            <div class="text-zinc-200 font-semibold mt-0.5">Task Deliverables</div>
            <div class="text-[10px] text-zinc-400 mt-0.5">Fixed Price per Task</div>
        </div>
        <div class="p-3 rounded-xl bg-[#12141C] border border-emerald-500/20 text-xs">
            <div class="text-[10px] font-mono font-bold text-emerald-400 uppercase">3. Per Project</div>
            <div class="text-zinc-200 font-semibold mt-0.5">Fixed Lump-Sum</div>
            <div class="text-[10px] text-zinc-400 mt-0.5">Project Budget Cap</div>
        </div>
        <div class="p-3 rounded-xl bg-[#12141C] border border-amber-500/20 text-xs">
            <div class="text-[10px] font-mono font-bold text-amber-400 uppercase">4. Monthly</div>
            <div class="text-zinc-200 font-semibold mt-0.5">Retainer Agreement</div>
            <div class="text-[10px] text-zinc-400 mt-0.5">Monthly Fee + Hours</div>
        </div>
    </div>

    <!-- Projects Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($projects as $proj)
            <div class="rounded-2xl bg-[#12141C] border border-white/[0.07] p-5 shadow-lg shadow-black/30 hover:border-violet-500/30 transition-all flex flex-col justify-between group">
                <div>
                    <!-- Top row: Status & Type -->
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold border" style="border-color: {{ $proj->color }}40; color: {{ $proj->color }}; background-color: {{ $proj->color }}15;">
                            <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $proj->color }}"></span>
                            {{ $proj->billing_type->shortCode() }} · {{ $proj->billing_type->label() }}
                        </span>

                        <x-status-pill :variant="$proj->status->value === 'active' ? 'emerald' : 'zinc'" size="xs">
                            {{ $proj->status->label() }}
                        </x-status-pill>
                    </div>

                    <!-- Title & Client -->
                    <a href="/projects/{{ $proj->slug }}" class="block">
                        <h3 class="text-base font-bold text-white group-hover:text-violet-300 transition-colors">
                            {{ $proj->name }}
                        </h3>
                        <p class="text-xs text-zinc-400 mt-0.5 flex items-center justify-between">
                            <span>Client: <strong class="text-zinc-200 font-medium">{{ $proj->client->name }}</strong></span>
                            <span class="font-mono text-zinc-300 font-semibold text-[11px]">{{ $proj->billing_rate_summary }}</span>
                        </p>
                    </a>

                    <!-- Budget & Burn Bar -->
                    <div class="mt-4 pt-3 border-t border-white/[0.05]">
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="text-zinc-400">Effort Consumption</span>
                            <span class="font-mono font-medium text-zinc-200" data-mono>{{ $proj->burn_percentage }}%</span>
                        </div>

                        <div class="h-2 w-full bg-zinc-800/80 rounded-full overflow-hidden p-0.5">
                            <div class="h-full rounded-full transition-all {{ $proj->burn_color_class }}" style="width: {{ min(100, $proj->burn_percentage) }}%;"></div>
                        </div>

                        <div class="mt-2 flex items-center justify-between text-[11px] text-zinc-400 font-mono" data-mono>
                            <span>{{ $proj->logged_hours }}h logged</span>
                            <span>{{ $proj->budget_hours ? $proj->budget_hours . 'h cap' : 'No cap' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer row: Task count & link -->
                <div class="mt-5 pt-3 border-t border-white/[0.05] flex items-center justify-between text-xs">
                    <span class="text-zinc-400">
                        <span class="font-semibold text-zinc-200">{{ $proj->tasks->count() }}</span> tasks ({{ $proj->tasks->where('status', 'done')->count() }} done)
                    </span>

                    <a href="/projects/{{ $proj->slug }}" class="inline-flex items-center gap-1 font-medium text-violet-400 hover:text-violet-300">
                        <span>Workspace</span>
                        <span>→</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-zinc-500 rounded-2xl bg-[#12141C] border border-white/[0.07]">
                No projects found. Click "New Project" to create your first workspace.
            </div>
        @endforelse
    </div>

    <!-- Create Project Modal with 4 Flexible Rates -->
    <div
        x-data="{ show: @entangle('showCreateModal'), selectedBilling: @entangle('billingType') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
    >
        <div
            @click.away="$wire.set('showCreateModal', false)"
            class="w-full max-w-xl bg-[#12141C] border border-white/[0.1] rounded-2xl p-6 shadow-2xl relative"
        >
            <div class="flex items-center justify-between pb-4 border-b border-white/[0.08]">
                <div>
                    <h3 class="text-base font-semibold text-white">Create New Project Workspace</h3>
                    <p class="text-xs text-zinc-400 mt-0.5">Configure project scoping and select one of 4 flexible rate models.</p>
                </div>
                <button type="button" wire:click="$set('showCreateModal', false)" class="text-zinc-400 hover:text-white">✕</button>
            </div>

            @if($errorMessage)
                <div class="mt-4 p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-xs text-rose-300">
                    {{ $errorMessage }}
                </div>
            @endif

            <div class="space-y-4 mt-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-zinc-400 mb-1.5">Client</label>
                        <select
                            wire:model="clientId"
                            class="w-full bg-[#171A24] text-xs font-medium text-zinc-200 border border-white/[0.08] rounded-xl px-3 py-2.5 focus:outline-none focus:border-violet-500"
                        >
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->currency }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-400 mb-1.5">Project Color</label>
                        <input
                            type="color"
                            wire:model="color"
                            class="w-full h-10 bg-[#171A24] border border-white/[0.08] rounded-xl px-1 py-1 cursor-pointer"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-400 mb-1.5">Project Name</label>
                    <input
                        type="text"
                        wire:model.live="name"
                        placeholder="e.g. Next-Gen Mobile API"
                        class="w-full bg-[#171A24] text-xs text-zinc-200 border border-white/[0.08] rounded-xl px-3 py-2.5 focus:outline-none focus:border-violet-500"
                    />
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-400 mb-1.5">Slug (used in CLI & URL)</label>
                    <input
                        type="text"
                        wire:model="slug"
                        placeholder="e.g. next-gen-mobile"
                        class="w-full bg-[#171A24] text-xs text-zinc-200 font-mono border border-white/[0.08] rounded-xl px-3 py-2.5 focus:outline-none focus:border-violet-500"
                    />
                </div>

                <!-- 4 Flexible Rate Model Segmented Card Selector -->
                <div>
                    <label class="block text-xs font-medium text-zinc-300 mb-2">Select Flexible Rate Model</label>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <!-- Option 1: Per Hour -->
                        <label
                            class="p-3 rounded-xl border cursor-pointer transition-all flex flex-col justify-between"
                            :class="selectedBilling === 'per_hour' ? 'bg-violet-500/10 border-violet-500 text-white' : 'bg-[#171A24] border-white/[0.06] text-zinc-400 hover:border-white/[0.15]'"
                        >
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-xs" :class="selectedBilling === 'per_hour' ? 'text-violet-300' : 'text-zinc-200'">1. Per Hour</span>
                                <input type="radio" wire:model.live="billingType" value="per_hour" class="text-violet-600 focus:ring-0" />
                            </div>
                            <span class="text-[11px] text-zinc-400">Track exact elapsed time; bill Hours × Rate</span>
                        </label>

                        <!-- Option 2: Per Task -->
                        <label
                            class="p-3 rounded-xl border cursor-pointer transition-all flex flex-col justify-between"
                            :class="selectedBilling === 'per_task' ? 'bg-blue-500/10 border-blue-500 text-white' : 'bg-[#171A24] border-white/[0.06] text-zinc-400 hover:border-white/[0.15]'"
                        >
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-xs" :class="selectedBilling === 'per_task' ? 'text-blue-300' : 'text-zinc-200'">2. Per Task</span>
                                <input type="radio" wire:model.live="billingType" value="per_task" class="text-blue-600 focus:ring-0" />
                            </div>
                            <span class="text-[11px] text-zinc-400">Tasks have fixed prices; measure effective hourly rate</span>
                        </label>

                        <!-- Option 3: Per Project -->
                        <label
                            class="p-3 rounded-xl border cursor-pointer transition-all flex flex-col justify-between"
                            :class="selectedBilling === 'per_project' ? 'bg-emerald-500/10 border-emerald-500 text-white' : 'bg-[#171A24] border-white/[0.06] text-zinc-400 hover:border-white/[0.15]'"
                        >
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-xs" :class="selectedBilling === 'per_project' ? 'text-emerald-300' : 'text-zinc-200'">3. Per Project</span>
                                <input type="radio" wire:model.live="billingType" value="per_project" class="text-emerald-600 focus:ring-0" />
                            </div>
                            <span class="text-[11px] text-zinc-400">Fixed total lump-sum fee for whole deliverable</span>
                        </label>

                        <!-- Option 4: Monthly -->
                        <label
                            class="p-3 rounded-xl border cursor-pointer transition-all flex flex-col justify-between"
                            :class="selectedBilling === 'monthly' ? 'bg-amber-500/10 border-amber-500 text-white' : 'bg-[#171A24] border-white/[0.06] text-zinc-400 hover:border-white/[0.15]'"
                        >
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-xs" :class="selectedBilling === 'monthly' ? 'text-amber-300' : 'text-zinc-200'">4. Monthly</span>
                                <input type="radio" wire:model.live="billingType" value="monthly" class="text-amber-600 focus:ring-0" />
                            </div>
                            <span class="text-[11px] text-zinc-400">Recurring monthly retainer fee with hours cap</span>
                        </label>
                    </div>
                </div>

                <!-- Conditional Dynamic Rate Inputs based on Selected Billing Model -->
                <div class="p-3.5 rounded-xl bg-[#171A24] border border-white/[0.06]">
                    <!-- Per Hour Fields -->
                    <template x-if="selectedBilling === 'per_hour'">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-zinc-300 mb-1">Hourly Rate (Override)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    wire:model="hourlyRate"
                                    placeholder="e.g. 150.00"
                                    class="w-full bg-[#12141C] text-xs text-zinc-200 font-mono border border-white/[0.08] rounded-xl px-3 py-2 focus:outline-none focus:border-violet-500"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-300 mb-1">Budget Hours Cap</label>
                                <input
                                    type="number"
                                    step="1"
                                    wire:model="budgetHours"
                                    placeholder="e.g. 40"
                                    class="w-full bg-[#12141C] text-xs text-zinc-200 font-mono border border-white/[0.08] rounded-xl px-3 py-2 focus:outline-none focus:border-violet-500"
                                />
                            </div>
                        </div>
                    </template>

                    <!-- Per Task Fields -->
                    <template x-if="selectedBilling === 'per_task'">
                        <div>
                            <div class="text-xs text-blue-300 font-semibold mb-1">Task-Based Fixed Deliverables</div>
                            <p class="text-[11px] text-zinc-400 mb-2">Each Kanban task will have its own individual fixed price (e.g. RM 500 for auth, RM 1,000 for checkout). You can also set an optional total hours target.</p>
                            <div>
                                <label class="block text-xs font-medium text-zinc-300 mb-1">Estimated Total Hours Target</label>
                                <input
                                    type="number"
                                    step="1"
                                    wire:model="budgetHours"
                                    placeholder="e.g. 50"
                                    class="w-full bg-[#12141C] text-xs text-zinc-200 font-mono border border-white/[0.08] rounded-xl px-3 py-2 focus:outline-none focus:border-blue-500"
                                />
                            </div>
                        </div>
                    </template>

                    <!-- Per Project Fields -->
                    <template x-if="selectedBilling === 'per_project'">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-zinc-300 mb-1">Total Fixed Project Fee</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    wire:model="fixedBudgetAmount"
                                    placeholder="e.g. 10000.00"
                                    class="w-full bg-[#12141C] text-xs text-zinc-200 font-mono border border-white/[0.08] rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-500"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-300 mb-1">Estimated Hours</label>
                                <input
                                    type="number"
                                    step="1"
                                    wire:model="budgetHours"
                                    placeholder="e.g. 80"
                                    class="w-full bg-[#12141C] text-xs text-zinc-200 font-mono border border-white/[0.08] rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-500"
                                />
                            </div>
                        </div>
                    </template>

                    <!-- Monthly Fields -->
                    <template x-if="selectedBilling === 'monthly'">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-zinc-300 mb-1">Monthly Retainer Amount</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    wire:model="monthlyRetainerAmount"
                                    placeholder="e.g. 5000.00"
                                    class="w-full bg-[#12141C] text-xs text-zinc-200 font-mono border border-white/[0.08] rounded-xl px-3 py-2 focus:outline-none focus:border-amber-500"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-300 mb-1">Monthly Hours Allowance</label>
                                <input
                                    type="number"
                                    step="1"
                                    wire:model="budgetHours"
                                    placeholder="e.g. 40"
                                    class="w-full bg-[#12141C] text-xs text-zinc-200 font-mono border border-white/[0.08] rounded-xl px-3 py-2 focus:outline-none focus:border-amber-500"
                                />
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-white/[0.08]">
                <button
                    type="button"
                    wire:click="$set('showCreateModal', false)"
                    class="px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-xs font-medium text-zinc-300 transition-colors"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    wire:click="createProject"
                    class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-xs font-semibold text-white shadow-lg shadow-violet-600/20 transition-all cursor-pointer"
                >
                    Create Workspace
                </button>
            </div>
        </div>
    </div>
</div>
