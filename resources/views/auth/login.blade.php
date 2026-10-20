<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — FreelanceOS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#090A0F] text-zinc-100 min-h-screen antialiased flex items-center justify-center selection:bg-violet-500/30 selection:text-violet-200">

    <div class="w-full max-w-sm px-4">

        <!-- Logo -->
        <div class="flex flex-col items-center mb-10">
            <div class="h-14 w-14 rounded-2xl bg-gradient-to-tr from-violet-600 to-indigo-500 flex items-center justify-center shadow-2xl shadow-violet-500/30 mb-4">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">
                Freelance<span class="text-violet-400">OS</span>
            </h1>
            <p class="text-xs text-zinc-500 font-mono mt-1 tracking-widest uppercase">Solo Core</p>
        </div>

        <!-- Card -->
        <div class="bg-[#0D0F17] border border-white/[0.07] rounded-2xl p-8 shadow-2xl">
            <h2 class="text-lg font-semibold text-white mb-1">Welcome back</h2>
            <p class="text-sm text-zinc-400 mb-8">Sign in to your workspace</p>

            <!-- Error message -->
            <div id="error-msg" class="hidden mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-sm text-red-400"></div>

            <!-- Google Sign-In Button -->
            <button
                id="google-signin-btn"
                type="button"
                class="w-full flex items-center justify-center gap-3 px-4 py-3 rounded-xl bg-white hover:bg-zinc-100 text-zinc-900 font-semibold text-sm transition-all shadow-lg hover:shadow-xl active:scale-[0.98]"
            >
                <!-- Google SVG -->
                <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                <span id="btn-text">Continue with Google</span>
            </button>

            <p class="text-center text-xs text-zinc-600 mt-6">
                Only you can access this workspace.<br>Your data stays private and secure.
            </p>
        </div>
    </div>

    <!-- Firebase SDK -->
    <script type="module">
        import { initializeApp } from 'https://www.gstatic.com/firebasejs/11.0.0/firebase-app.js';
        import { getAuth, signInWithPopup, GoogleAuthProvider } from 'https://www.gstatic.com/firebasejs/11.0.0/firebase-auth.js';

        const firebaseConfig = {
            apiKey:            "{{ config('services.firebase.api_key') }}",
            authDomain:        "{{ config('services.firebase.auth_domain') }}",
            projectId:         "{{ config('services.firebase.project_id') }}",
            storageBucket:     "{{ config('services.firebase.storage_bucket') }}",
            messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
            appId:             "{{ config('services.firebase.app_id') }}",
        };

        const app      = initializeApp(firebaseConfig);
        const auth     = getAuth(app);
        const provider = new GoogleAuthProvider();

        const btn     = document.getElementById('google-signin-btn');
        const btnText = document.getElementById('btn-text');
        const errBox  = document.getElementById('error-msg');

        btn.addEventListener('click', async () => {
            btn.disabled = true;
            btnText.textContent = 'Signing in…';
            errBox.classList.add('hidden');

            try {
                const result  = await signInWithPopup(auth, provider);
                const idToken = await result.user.getIdToken();

                const res = await fetch('{{ route("auth.firebase.callback") }}', {
                    method:  'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':           'application/json',
                    },
                    body: JSON.stringify({ id_token: idToken }),
                });

                let data = {};
                try {
                    data = await res.json();
                } catch {
                    // response was not json
                }

                if (!res.ok) {
                    throw new Error(data.error ?? data.message ?? 'Authentication failed.');
                }

                window.location.href = data.redirect;

            } catch (err) {
                errBox.textContent = err.message;
                errBox.classList.remove('hidden');
                btn.disabled = false;
                btnText.textContent = 'Continue with Google';
            }
        });
    </script>

</body>
</html>
