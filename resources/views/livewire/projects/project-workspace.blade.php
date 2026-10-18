<div class="space-y-6">
    <!-- Back breadcrumb & Project Burn Header -->
    <div class="rounded-2xl bg-[#12141C] border border-white/[0.07] p-6 shadow-xl shadow-black/30">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Left: Project Info -->
            <div class="space-y-2">
                <div class="flex items-center gap-2 text-xs text-zinc-400">
                    <a href="/projects" class="hover:text-white transition-colors">Projects</a>
                    <span>/</span>
                    <span class="text-zinc-300">{{ $project->client->name }}</span>
                </div>

                <div class="flex items-center gap-3">
                    <span class="h-4 w-4 rounded-full flex-shrink-0" style="background-color: {{ $project->color }}"></span>
                    <h1 class="text-2xl font-bold tracking-tight text-white">{{ $project->name }}</h1>
                    <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-zinc-800 text-zinc-400 border border-white/[0.05]">
                        {{ $project->slug }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-3 text-xs text-zinc-400 pt-1">
                    <x-status-pill :variant="$project->billing_type->badgeVariant()" size="sm">
                        {{ $project->billing_type->shortCode() }} · {{ $project->billing_type->label() }}
                    </x-status-pill>

                    <span>Model Rate: <strong class="text-zinc-200 font-mono">{{ $project->billing_rate_summary }}</strong></span>
                    <span>·</span>
                    <span>Effective Hourly Yield: <strong class="text-emerald-400 font-mono font-bold">{{ $project->client->currency }} {{ number_format($project->effective_hourly_rate, 2) }}/h</strong></span>
                    <span>·</span>
                    <span>Unbilled Pool: <strong class="text-amber-400 font-mono">{{ $project->client->currency }} {{ number_format($unbilledAmount, 2) }}</strong></span>
                </div>
            </div>

            <!-- Right: Budget Consumption & Quick Action -->
            <div class="lg:w-80 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="text-zinc-400">
                            {{ $project->billing_type->value === 'monthly' ? 'Monthly Hours Allowance' : 'Budget Effort Burn' }}
                        </span>
                        <span class="font-mono font-bold text-zinc-100" data-mono>{{ $project->burn_percentage }}%</span>
                    </div>

                    <div class="h-2.5 w-full bg-zinc-800 rounded-full overflow-hidden p-0.5">
                        <div class="h-full rounded-full transition-all duration-500 {{ $project->burn_color_class }}" style="width: {{ min(100, $project->burn_percentage) }}%;"></div>
                    </div>

                    <div class="mt-2 flex items-center justify-between text-[11px] text-zinc-400 font-mono" data-mono>
                        <span>{{ $project->logged_hours }}h logged</span>
                        <span>{{ $project->budget_hours ? $project->budget_hours . 'h cap' : 'No limit' }}</span>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        wire:click="openTaskModal('todo')"
                        class="px-3.5 py-1.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-semibold shadow-lg shadow-violet-600/20 active:scale-95 transition-all cursor-pointer flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        <span>Add Task</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if($successMessage)
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-xs text-emerald-300 flex items-center justify-between">
            <span>{{ $successMessage }}</span>
            <button type="button" wire:click="$set('successMessage', '')" class="text-emerald-400 hover:text-emerald-200">✕</button>
        </div>
    @endif

    <!-- Kanban Board Container (5 Columns) -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 items-start">
        @foreach($columns as $colKey => $col)
            <div class="rounded-2xl bg-[#12141C] border border-white/[0.07] p-3.5 flex flex-col min-h-[500px]">
                <!-- Column Header -->
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-white/[0.06]">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-300">{{ $col['title'] }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-zinc-800 text-zinc-400 font-semibold">
                            {{ $col['tasks']->count() }}
                        </span>
                    </div>

                    <button
                        type="button"
                        wire:click="openTaskModal('{{ $colKey }}')"
                        title="Add task in this column"
                        class="text-zinc-500 hover:text-zinc-200 p-1 rounded hover:bg-white/[0.05] transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </button>
                </div>

                <!-- Column Cards List -->
                <div class="space-y-3 flex-1 overflow-y-auto">
                    @forelse($col['tasks'] as $task)
                        <div class="rounded-xl bg-[#171A24] border border-white/[0.06] p-3.5 shadow hover:border-violet-500/40 transition-all flex flex-col justify-between group">
                            <div>
                                <!-- Priority, Task Price & Quick Launch -->
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @php
                                        $prioVariant = match($task->priority) {
                                            'high' => 'rose',
                                            'medium' => 'amber',
                                            default => 'zinc',
                                        };
                                        @endphp
                                        <x-status-pill :variant="$prioVariant" size="xs">
                                            {{ ucfirst($task->priority) }}
                                        </x-status-pill>

                                        @if($task->fixed_amount)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-blue-500/10 text-blue-300 border border-blue-500/20">
                                                {{ $project->client->currency }} {{ number_format($task->fixed_amount, 2) }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Quick Launch Stopwatch Button -->
                                    <button
                                        type="button"
                                        wire:click="startTaskTimer({{ $task->id }})"
                                        title="Start active timer on this task"
                                        class="h-6 w-6 rounded-md bg-emerald-500/10 hover:bg-emerald-500 hover:text-black text-emerald-400 border border-emerald-500/20 flex items-center justify-center transition-all cursor-pointer flex-shrink-0"
                                    >
                                        <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </button>
                                </div>

                                <!-- Title -->
                                <h4 class="text-xs font-semibold text-zinc-100 group-hover:text-white transition-colors leading-snug">
                                    {{ $task->title }}
                                </h4>

                                @if($task->description)
                                    <p class="text-[11px] text-zinc-400 mt-1 line-clamp-2">{{ $task->description }}</p>
                                @endif

                                <div class="mt-2 flex items-center justify-between flex-wrap gap-1">
                                    @if($task->git_ref)
                                        <div class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-zinc-900 border border-white/[0.05] text-[10px] font-mono text-zinc-400">
                                            <svg class="w-2.5 h-2.5 text-zinc-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                                            <span>{{ $task->git_ref }}</span>
                                        </div>
                                    @endif

                                    @if($task->fixed_amount && $task->logged_hours > 0)
                                        <div class="text-[10px] font-mono text-emerald-400 font-semibold" title="Effective hourly yield">
                                            ⚡ {{ $project->client->currency }} {{ number_format($task->effective_hourly_rate, 0) }}/h
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Footer: Hours & Column Move Selector -->
                            <div class="mt-3 pt-2.5 border-t border-white/[0.05] flex items-center justify-between text-[11px] text-zinc-400">
                                <span class="font-mono text-zinc-300" data-mono>
                                    {{ $task->logged_hours }}h / {{ $task->estimated_hours ? $task->estimated_hours . 'h' : '—' }}
                                </span>

                                <!-- Move Column Select -->
                                <select
                                    wire:change="updateTaskStatus({{ $task->id }}, $event.target.value)"
                                    class="bg-transparent text-[10px] text-zinc-400 hover:text-zinc-200 border-none focus:ring-0 cursor-pointer text-right"
                                >
                                    <option value="" disabled selected>Move →</option>
                                    <option value="backlog">Backlog</option>
                                    <option value="todo">To Do</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="review">In Review</option>
                                    <option value="done">Done</option>
                                </select>
                            </div>
                        </div>
                    @empty
                        <div class="h-28 border border-dashed border-white/[0.08] rounded-xl flex items-center justify-center text-[11px] text-zinc-500">
                            Empty column
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <!-- Create Task Modal -->
    <div
        x-data="{ show: @entangle('showTaskModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
    >
        <div
            @click.away="$wire.set('showTaskModal', false)"
            class="w-full max-w-lg bg-[#12141C] border border-white/[0.1] rounded-2xl p-6 shadow-2xl relative"
        >
            <div class="flex items-center justify-between pb-4 border-b border-white/[0.08]">
                <div>
                    <h3 class="text-base font-semibold text-white">Add Kanban Task</h3>
                    <p class="text-xs text-zinc-400 mt-0.5">Scoping task under {{ $project->name }}</p>
                </div>
                <button type="button" wire:click="$set('showTaskModal', false)" class="text-zinc-400 hover:text-white">✕</button>
            </div>

            @if($errorMessage)
                <div class="mt-4 p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-xs text-rose-300">
                    {{ $errorMessage }}
                </div>
            @endif

            <div class="space-y-4 mt-4">
                <div>
                    <label class="block text-xs font-medium text-zinc-400 mb-1.5">Task Title</label>
                    <input
                        type="text"
                        wire:model="taskTitle"
                        placeholder="e.g. Implement Webhook retry backoff"
                        class="w-full bg-[#171A24] text-xs text-zinc-200 border border-white/[0.08] rounded-xl px-3 py-2.5 focus:outline-none focus:border-violet-500"
                    />
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-400 mb-1.5">Description / Scope Notes</label>
                    <textarea
                        wire:model="taskDescription"
                        rows="2"
                        placeholder="Acceptance criteria or implementation details..."
                        class="w-full bg-[#171A24] text-xs text-zinc-200 border border-white/[0.08] rounded-xl px-3 py-2 focus:outline-none focus:border-violet-500"
                    ></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-zinc-400 mb-1.5">Initial Column</label>
                        <select
                            wire:model="taskStatus"
                            class="w-full bg-[#171A24] text-xs font-medium text-zinc-200 border border-white/[0.08] rounded-xl px-3 py-2.5 focus:outline-none focus:border-violet-500"
                        >
                            <option value="backlog">Backlog</option>
                            <option value="todo">To Do</option>
                            <option value="in_progress">In Progress</option>
                            <option value="review">In Review</option>
                            <option value="done">Done</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-400 mb-1.5">Priority</label>
                        <select
                            wire:model="taskPriority"
                            class="w-full bg-[#171A24] text-xs font-medium text-zinc-200 border border-white/[0.08] rounded-xl px-3 py-2.5 focus:outline-none focus:border-violet-500"
                        >
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-zinc-400 mb-1.5">
                            Fixed Task Price
                            <span class="text-blue-400 font-mono text-[10px]">(Per Task)</span>
                        </label>
                        <input
                            type="number"
                            step="0.01"
                            wire:model="taskFixedAmount"
                            placeholder="e.g. 500.00"
                            class="w-full bg-[#171A24] text-xs text-zinc-200 font-mono border border-white/[0.08] rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-400 mb-1.5">Estimated Hours</label>
                        <input
                            type="number"
                            step="0.5"
                            wire:model="taskEstimatedHours"
                            class="w-full bg-[#171A24] text-xs text-zinc-200 font-mono border border-white/[0.08] rounded-xl px-3 py-2.5 focus:outline-none focus:border-violet-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-400 mb-1.5">Git Ref / Branch</label>
                        <input
                            type="text"
                            wire:model="taskGitRef"
                            placeholder="feat/webhooks"
                            class="w-full bg-[#171A24] text-xs text-zinc-200 font-mono border border-white/[0.08] rounded-xl px-3 py-2.5 focus:outline-none focus:border-violet-500"
                        />
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-white/[0.08]">
                <button
                    type="button"
                    wire:click="$set('showTaskModal', false)"
                    class="px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-xs font-medium text-zinc-300 transition-colors"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    wire:click="saveTask"
                    class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-xs font-semibold text-white shadow-lg shadow-violet-600/20 transition-all cursor-pointer"
                >
                    Add Task
                </button>
            </div>
        </div>
    </div>
</div>
