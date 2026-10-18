<div
    x-data="{
        isOpen: @entangle('isOpen'),
        openModal() {
            this.isOpen = true;
            this.$nextTick(() => {
                if (this.$refs.commandInput) {
                    this.$refs.commandInput.focus();
                }
            });
        },
        closeModal() {
            this.isOpen = false;
        }
    }"
    @keydown.window.prevent.cmd.k="openModal()"
    @keydown.window.prevent.ctrl.k="openModal()"
    @keydown.escape.window="closeModal()"
>
    <!-- Modal Backdrop and Container -->
    <div
        x-show="isOpen"
        x-cloak
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto bg-black/75 backdrop-blur-md flex items-start justify-center pt-20 px-4 sm:px-6"
    >
        <div
            @click.away="closeModal()"
            class="relative w-full max-w-2xl bg-[#12141C] border border-white/[0.12] rounded-2xl shadow-2xl shadow-black overflow-hidden transform transition-all"
        >
            <!-- Search / Command Input Bar -->
            <div class="flex items-center px-4 py-3.5 border-b border-white/[0.08]">
                <svg class="w-5 h-5 text-violet-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    x-ref="commandInput"
                    type="text"
                    wire:model="query"
                    wire:keydown.enter="executeCommand"
                    placeholder="Type a command (e.g. 'start pinnacle-cms Fix auth', 'stop', 'goto invoices')..."
                    class="w-full bg-transparent text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none"
                />
                <button
                    type="button"
                    @click="closeModal()"
                    class="ml-2 px-2 py-1 text-[11px] font-mono font-medium text-zinc-400 bg-zinc-800/80 rounded border border-zinc-700/60 hover:text-zinc-200"
                >
                    ESC
                </button>
            </div>

            @if($feedbackMessage)
                <div class="px-4 py-2 bg-amber-500/10 border-b border-amber-500/20 text-xs text-amber-300">
                    {{ $feedbackMessage }}
                </div>
            @endif

            <!-- Suggestions and Quick Action Guide -->
            <div class="p-4 space-y-4 max-h-96 overflow-y-auto">
                <!-- Command Syntax Cheat Sheet -->
                <div>
                    <div class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-2">
                        Quick Syntax Shortcuts
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        <button
                            type="button"
                            wire:click="$set('query', 'start ')"
                            class="flex items-center justify-between p-2.5 rounded-lg bg-[#171A24] border border-white/[0.06] hover:border-violet-500/40 text-left transition-colors group"
                        >
                            <span class="text-zinc-200 group-hover:text-violet-300 font-mono">start &lt;slug&gt; &lt;desc&gt;</span>
                            <span class="text-zinc-500 text-[11px]">Start timer</span>
                        </button>

                        <button
                            type="button"
                            wire:click="$set('query', 'pause')"
                            class="flex items-center justify-between p-2.5 rounded-lg bg-[#171A24] border border-white/[0.06] hover:border-amber-500/40 text-left transition-colors group"
                        >
                            <span class="text-zinc-200 group-hover:text-amber-300 font-mono">pause</span>
                            <span class="text-zinc-500 text-[11px]">Pause running timer</span>
                        </button>

                        <button
                            type="button"
                            wire:click="$set('query', 'resume')"
                            class="flex items-center justify-between p-2.5 rounded-lg bg-[#171A24] border border-white/[0.06] hover:border-emerald-500/40 text-left transition-colors group"
                        >
                            <span class="text-zinc-200 group-hover:text-emerald-300 font-mono">resume</span>
                            <span class="text-zinc-500 text-[11px]">Resume paused timer</span>
                        </button>

                        <button
                            type="button"
                            wire:click="executeCommand"
                            wire:click="$set('query', 'stop')"
                            class="flex items-center justify-between p-2.5 rounded-lg bg-[#171A24] border border-white/[0.06] hover:border-rose-500/40 text-left transition-colors group"
                        >
                            <span class="text-zinc-200 group-hover:text-rose-400 font-mono">stop</span>
                            <span class="text-zinc-500 text-[11px]">Stop & save timer</span>
                        </button>

                        <button
                            type="button"
                            wire:click="$set('query', 'log 1h30m ')"
                            class="flex items-center justify-between p-2.5 rounded-lg bg-[#171A24] border border-white/[0.06] hover:border-emerald-500/40 text-left transition-colors group"
                        >
                            <span class="text-zinc-200 group-hover:text-emerald-400 font-mono">log &lt;dur&gt; &lt;slug&gt; &lt;desc&gt;</span>
                            <span class="text-zinc-500 text-[11px]">Manual log</span>
                        </button>

                        <button
                            type="button"
                            wire:click="$set('query', 'goto ')"
                            class="flex items-center justify-between p-2.5 rounded-lg bg-[#171A24] border border-white/[0.06] hover:border-blue-500/40 text-left transition-colors group"
                        >
                            <span class="text-zinc-200 group-hover:text-blue-400 font-mono">goto &lt;page&gt;</span>
                            <span class="text-zinc-500 text-[11px]">Jump to page</span>
                        </button>
                    </div>
                </div>

                <!-- Active Projects Quick Start -->
                <div>
                    <div class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-2">
                        Active Projects
                    </div>
                    <div class="space-y-1.5">
                        @foreach($projects as $proj)
                            <button
                                type="button"
                                wire:click="$set('query', 'start {{ $proj->slug }} ')"
                                class="w-full flex items-center justify-between p-2 rounded-lg hover:bg-white/[0.04] text-left transition-colors"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full" style="background-color: {{ $proj->color }}"></span>
                                    <span class="text-xs font-medium text-zinc-200">{{ $proj->name }}</span>
                                    <span class="text-[11px] font-mono text-zinc-500">({{ $proj->slug }})</span>
                                </div>
                                <span class="text-[11px] text-zinc-400">Click to start timer</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Footer info -->
            <div class="px-4 py-2.5 bg-[#0D0F17] border-t border-white/[0.08] flex items-center justify-between text-[11px] text-zinc-400">
                <div class="flex items-center gap-3">
                    <span>Press <kbd class="px-1.5 py-0.5 font-mono text-zinc-300 bg-zinc-800 rounded">Enter</kbd> to execute</span>
                    <span><kbd class="px-1.5 py-0.5 font-mono text-zinc-300 bg-zinc-800 rounded">Esc</kbd> to close</span>
                </div>
                <div class="flex items-center gap-1 font-mono text-violet-400">
                    FreelanceOS Fuzzy Command CLI
                </div>
            </div>
        </div>
    </div>
</div>
