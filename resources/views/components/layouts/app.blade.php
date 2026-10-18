<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'FreelanceOS — Bento FMS' }}</title>

    <!-- Preconnect Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#090A0F] text-zinc-100 min-h-screen antialiased flex selection:bg-violet-500/30 selection:text-violet-200">

    <!-- Global Sonner Toast Container -->
    <ol
        id="sonner-toast-container"
        position="bottom-right"
        max-toasts="4"
        rich-colors="true"
        theme="dark"
        expand="true"
    ></ol>

    <!-- Global Command Palette Modal (Cmd+K / Ctrl+K) -->
    <livewire:command-palette />

    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-[#0D0F17] border-r border-white/[0.07] hidden md:flex flex-col flex-shrink-0 z-40 justify-between h-screen sticky top-0">
        <div>
            <!-- Brand Header -->
            <div class="h-16 flex items-center justify-between px-6 border-b border-white/[0.07]">
                <a href="/" class="flex items-center gap-2.5 group">
                    <div class="h-8 w-8 rounded-lg bg-gradient-to-tr from-violet-600 to-indigo-500 flex items-center justify-center shadow-lg shadow-violet-500/20 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="font-bold text-sm tracking-tight text-white flex items-center gap-1.5">
                            Freelance<span class="text-violet-400">OS</span>
                        </span>
                        <span class="text-[10px] text-zinc-500 block -mt-0.5 tracking-wider font-mono">SOLO CORE</span>
                    </div>
                </a>
            </div>

            <!-- Quick Cmd+K Button -->
            <div class="px-4 pt-4">
                <button
                    type="button"
                    x-data
                    @click="$dispatch('open-command-palette')"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl bg-[#171A24] border border-white/[0.06] hover:border-violet-500/30 text-xs text-zinc-400 hover:text-zinc-200 transition-all shadow-inner"
                >
                    <span class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Commands...</span>
                    </span>
                    <span class="px-1.5 py-0.5 font-mono text-[10px] bg-zinc-800 text-zinc-400 rounded border border-white/[0.05]">⌘K</span>
                </button>
            </div>

            <!-- Main Navigation Links -->
            <nav class="p-4 space-y-1 text-sm font-medium">
                <a
                    href="/"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->is('/') ? 'bg-violet-600/10 text-violet-300 border border-violet-500/20 font-semibold' : 'text-zinc-400 hover:text-zinc-100 hover:bg-white/[0.03]' }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a
                    href="/timesheet"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->is('timesheet*') ? 'bg-violet-600/10 text-violet-300 border border-violet-500/20 font-semibold' : 'text-zinc-400 hover:text-zinc-100 hover:bg-white/[0.03]' }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Timesheet</span>
                </a>

                <a
                    href="/projects"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->is('projects*') ? 'bg-violet-600/10 text-violet-300 border border-violet-500/20 font-semibold' : 'text-zinc-400 hover:text-zinc-100 hover:bg-white/[0.03]' }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    <span>Projects & Tasks</span>
                </a>

                <a
                    href="/invoices"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->is('invoices*') ? 'bg-violet-600/10 text-violet-300 border border-violet-500/20 font-semibold' : 'text-zinc-400 hover:text-zinc-100 hover:bg-white/[0.03]' }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                    </svg>
                    <span>Invoices (PDF)</span>
                </a>

                <a
                    href="/settings"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->is('settings*') ? 'bg-violet-600/10 text-violet-300 border border-violet-500/20 font-semibold' : 'text-zinc-400 hover:text-zinc-100 hover:bg-white/[0.03]' }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Settings</span>
                </a>
            </nav>
        </div>

        <!-- Developer Profile & Local Environment Footer -->
        <div class="p-4 border-t border-white/[0.07]">
            <a href="/settings" class="block rounded-xl bg-[#171A24] hover:bg-[#1C202C] border border-white/[0.06] p-3 transition-colors">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="h-8 w-8 rounded-full bg-violet-500/20 text-violet-300 flex items-center justify-center font-bold text-xs flex-shrink-0">
                        {{ strtoupper(substr(\App\Models\Setting::get('freelancer_name', 'Alex Rivers'), 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-semibold text-white truncate">{{ \App\Models\Setting::get('freelancer_name', 'Alex Rivers') }}</div>
                        <div class="text-[10px] text-emerald-400 font-mono flex items-center gap-1">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            <span>{{ \App\Models\Setting::get('currency', 'MYR') }} • Active</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Persistent Sticky Header Stopwatch Timer Bar -->
        <header class="sticky top-0 z-30">
            <livewire:global-timer-bar />
        </header>

        <!-- Dynamic Body Page Content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
