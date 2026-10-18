<div
    x-data="{
        isRunning: @entangle('isRunning'),
        isPaused: @entangle('isPaused'),
        baseDuration: @entangle('baseDurationSeconds'),
        startedAt: @entangle('startedAtTimestamp'),
        description: @entangle('description'),
        elapsedSeconds: 0,
        formattedTime: '00:00:00',
        timerInterval: null,
        originalTitle: document.title,

        init() {
            this.updateTime();
            if (this.isRunning && !this.isPaused) {
                this.startTicking();
            }

            this.$watch('isRunning', (value) => {
                if (value && !this.isPaused) {
                    this.startTicking();
                } else {
                    this.stopTicking();
                    if (!value) {
                        document.title = 'FreelanceOS — Bento FMS';
                    }
                }
            });

            this.$watch('isPaused', (value) => {
                if (value) {
                    this.stopTicking();
                    this.updateTime();
                } else if (this.isRunning) {
                    this.startTicking();
                }
            });
        },

        startTicking() {
            this.stopTicking();
            this.updateTime();
            this.timerInterval = setInterval(() => {
                this.updateTime();
            }, 1000);
        },

        stopTicking() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
        },

        updateTime() {
            if (!this.isRunning) {
                this.formattedTime = '00:00:00';
                return;
            }

            if (this.isPaused) {
                const totalSec = Number(this.baseDuration) || 0;
                this.elapsedSeconds = totalSec;
                const h = String(Math.floor(totalSec / 3600)).padStart(2, '0');
                const m = String(Math.floor((totalSec % 3600) / 60)).padStart(2, '0');
                const s = String(totalSec % 60).padStart(2, '0');
                this.formattedTime = `${h}:${m}:${s}`;
                document.title = `❚❚ ${this.formattedTime} (Paused) — ${this.description || 'Active Timer'} | FreelanceOS`;
                return;
            }

            const base = Number(this.baseDuration) || 0;
            const diffMs = this.startedAt ? Math.max(0, Date.now() - this.startedAt) : 0;
            const totalSec = base + Math.floor(diffMs / 1000);
            this.elapsedSeconds = totalSec;

            const h = String(Math.floor(totalSec / 3600)).padStart(2, '0');
            const m = String(Math.floor((totalSec % 3600) / 60)).padStart(2, '0');
            const s = String(totalSec % 60).padStart(2, '0');

            this.formattedTime = `${h}:${m}:${s}`;
            document.title = `▶ ${this.formattedTime} — ${this.description || 'Active Timer'} | FreelanceOS`;
        }
    }"
    class="w-full bg-[#0D0F17]/95 backdrop-blur-md border-b border-white/[0.07] px-4 lg:px-8 py-3 transition-all duration-300 z-30"
>
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-3">
        <!-- Left: Project Selector + Description input with running/paused glow -->
        <div class="flex-1 w-full flex items-center gap-2">
            <!-- Project dropdown -->
            <div class="relative w-44 md:w-56 flex-shrink-0">
                <select
                    wire:model.defer="projectId"
                    :disabled="isRunning"
                    class="w-full bg-[#171A24] text-xs font-medium text-zinc-200 border border-white/[0.08] rounded-lg px-3 py-2 pr-8 focus:outline-none focus:border-violet-500 transition-colors disabled:opacity-60 cursor-pointer"
                >
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}">
                            {{ $proj->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Task Description & Git Ref -->
            <div
                class="flex-1 flex items-center bg-[#171A24] border rounded-lg px-3 py-1.5 transition-all duration-300"
                :class="{
                    'border-amber-500/40 shadow-[0_0_20px_-3px_rgba(245,158,11,0.2)]': isRunning && isPaused,
                    'border-emerald-500/40 shadow-[0_0_20px_-3px_rgba(16,185,129,0.2)]': isRunning && !isPaused,
                    'border-white/[0.08] focus-within:border-violet-500/50': !isRunning
                }"
            >
                <!-- Indicator dot (Pulsing green if running, amber pause bars if paused, grey if idle) -->
                <div class="flex items-center mr-2.5">
                    <template x-if="isRunning && !isPaused">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                    </template>
                    <template x-if="isRunning && isPaused">
                        <span class="inline-flex items-center justify-center h-3 w-3 rounded text-[9px] font-bold text-amber-400 bg-amber-500/20">
                            ❚❚
                        </span>
                    </template>
                    <template x-if="!isRunning">
                        <span class="h-2.5 w-2.5 rounded-full bg-zinc-600"></span>
                    </template>
                </div>

                <input
                    type="text"
                    wire:model.defer="description"
                    placeholder="What are you hacking on right now?"
                    class="w-full bg-transparent text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none"
                    @keydown.enter="if(!isRunning) { $wire.start() }"
                />

                <!-- Git ref badge/input -->
                <div class="hidden sm:flex items-center ml-2 pl-2 border-l border-white/[0.08]">
                    <span class="text-zinc-500 text-xs mr-1 font-mono">#</span>
                    <input
                        type="text"
                        wire:model.defer="gitRef"
                        placeholder="branch/ref"
                        class="w-24 bg-transparent text-xs text-zinc-300 placeholder-zinc-600 font-mono focus:outline-none"
                    />
                </div>
            </div>
        </div>

        <!-- Right: Billable Toggle, Live Digital Counter, Pause/Resume & Start/Stop Controls -->
        <div class="flex items-center justify-between md:justify-end w-full md:w-auto gap-3 flex-shrink-0">
            <!-- Billable Toggle -->
            <button
                type="button"
                wire:click="$toggle('isBillable')"
                :disabled="isRunning"
                title="Toggle Billable Rate"
                class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg border text-xs font-medium transition-all"
                :class="$wire.isBillable ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-zinc-800/80 text-zinc-500 border-zinc-700/50'"
            >
                <span class="font-mono font-bold">$</span>
                <span class="hidden sm:inline" x-text="$wire.isBillable ? 'Billable' : 'Non-billable'"></span>
            </button>

            <!-- Live Digital Stopwatch Counter -->
            <div class="flex items-center gap-2 px-3 py-1 rounded-lg bg-[#12141C] border border-white/[0.06]">
                <span
                    class="font-mono text-base font-semibold tracking-wider transition-colors"
                    :class="{
                        'text-amber-400': isRunning && isPaused,
                        'text-emerald-400': isRunning && !isPaused,
                        'text-zinc-400': !isRunning
                    }"
                    data-mono
                    x-text="formattedTime"
                >00:00:00</span>

                <template x-if="isRunning && isPaused">
                    <span class="text-[9px] font-mono uppercase tracking-wider text-amber-400 bg-amber-500/10 px-1.5 py-0.5 rounded border border-amber-500/20">
                        PAUSED
                    </span>
                </template>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex items-center gap-2">
                <!-- If Timer is NOT running: Show START -->
                <template x-if="!isRunning">
                    <button
                        type="button"
                        wire:click="start"
                        class="flex items-center gap-2 px-5 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-black font-semibold text-xs tracking-wide shadow-lg shadow-emerald-500/20 active:scale-95 transition-all cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        <span>Start</span>
                    </button>
                </template>

                <!-- If Timer IS running: Show PAUSE / RESUME + STOP -->
                <template x-if="isRunning">
                    <div class="flex items-center gap-2">
                        <!-- PAUSE BUTTON (shown when active) -->
                        <template x-if="!isPaused">
                            <button
                                type="button"
                                wire:click="pause"
                                title="Pause active timer"
                                class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-amber-500/15 hover:bg-amber-500 hover:text-black text-amber-300 border border-amber-500/30 font-semibold text-xs tracking-wide shadow-lg shadow-amber-500/10 active:scale-95 transition-all cursor-pointer"
                            >
                                <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                <span>Pause</span>
                            </button>
                        </template>

                        <!-- RESUME BUTTON (shown when paused) -->
                        <template x-if="isPaused">
                            <button
                                type="button"
                                wire:click="resume"
                                title="Resume timer"
                                class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-black font-semibold text-xs tracking-wide shadow-lg shadow-emerald-500/20 active:scale-95 transition-all cursor-pointer"
                            >
                                <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                <span>Resume</span>
                            </button>
                        </template>

                        <!-- STOP BUTTON -->
                        <button
                            type="button"
                            wire:click="stop"
                            title="Stop & save time log"
                            class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-rose-500 hover:bg-rose-400 text-white font-semibold text-xs tracking-wide shadow-lg shadow-rose-500/20 active:scale-95 transition-all cursor-pointer"
                        >
                            <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="1"/></svg>
                            <span>Stop</span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
