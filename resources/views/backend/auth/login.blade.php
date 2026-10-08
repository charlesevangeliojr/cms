<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="turbo-cache-control" content="no-cache">

    <title>Login — CMS</title>

    <link rel="icon" type="image/png" href="{{ asset('images/cms-logo.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap"
        rel="stylesheet"
    >

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    </script>

    <script type="module">
        import * as Turbo from 'https://cdn.jsdelivr.net/npm/@hotwired/turbo@8.0.23/+esm';
        Turbo.start();
    </script>

    <style>
        [data-login-page] {
            min-height: 100vh;
            min-height: 100dvh;
        }

        .login-background {
            background-image:
                linear-gradient(
                    135deg,
                    rgba(2, 6, 23, .90) 0%,
                    rgba(15, 23, 42, .58) 48%,
                    rgba(30, 41, 59, .34) 100%
                ),
                url('{{ asset('images/login-background.png') }}');
        }

        .login-grid-pattern {
            background-image:
                linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px);
            background-size: 44px 44px;
            mask-image: linear-gradient(to bottom, black, transparent 85%);
            -webkit-mask-image: linear-gradient(to bottom, black, transparent 85%);
        }

        .login-glow {
            background:
                radial-gradient(
                    circle,
                    rgba(99, 102, 241, .32) 0%,
                    rgba(99, 102, 241, .10) 35%,
                    transparent 70%
                );
            filter: blur(10px);
        }

        .login-input:-webkit-autofill,
        .login-input:-webkit-autofill:hover,
        .login-input:-webkit-autofill:focus {
            -webkit-text-fill-color: #0f172a;
            -webkit-box-shadow: 0 0 0 1000px #ffffff inset;
            transition: background-color 5000s ease-in-out 0s;
        }
    </style>
</head>

<body class="bg-slate-950 font-sans text-slate-900 antialiased">

<main data-login-page class="w-full overflow-hidden">

    <div class="grid min-h-screen min-h-[100dvh] w-full bg-white lg:h-screen lg:min-h-0 lg:grid-cols-[54%_46%] lg:overflow-hidden xl:grid-cols-[58%_42%]">

        {{-- =========================================================
             LEFT BRAND / IMAGE PANEL
        ========================================================== --}}
        <section
            class="login-background relative hidden overflow-hidden bg-slate-950 bg-cover bg-center text-white lg:flex lg:h-full lg:min-h-0 lg:flex-col"
        >

            {{-- Decorative overlays --}}
            <div class="login-grid-pattern pointer-events-none absolute inset-0"></div>

            <div
                class="login-glow pointer-events-none absolute -left-40 top-[18%] h-[520px] w-[520px] rounded-full"
            ></div>

            <div
                class="pointer-events-none absolute -bottom-48 -right-40 h-[500px] w-[500px] rounded-full bg-cyan-400/10 blur-3xl"
            ></div>

            <div
                class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-slate-950/20"
            ></div>


            {{-- Header / Logo --}}
            <header class="relative z-10 flex items-center justify-between px-10 py-6 xl:px-14 xl:py-7">

                <div class="flex items-center gap-4">

                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl border border-white/10 bg-white/10 p-2 shadow-2xl shadow-black/20 backdrop-blur-xl"
                    >
                        <img
                            src="{{ asset('images/cms-logo.png') }}"
                            alt="CMS Workspace logo"
                            class="h-full w-full object-contain"
                        >
                    </div>

                    <div>
                        <p class="text-[21px] font-bold tracking-tight">
                            CMS Workspace
                        </p>

                        <p class="mt-0.5 text-xs font-medium text-white/55">
                            Content Management System
                        </p>
                    </div>

                </div>


            </header>


            {{-- Main content --}}
            <div class="relative z-10 flex min-h-0 flex-1 items-center overflow-y-auto px-10 py-4 xl:px-14 xl:py-5">

                <div class="max-w-2xl">

                    <div
                        class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[.07] px-4 py-2 backdrop-blur-md"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-cyan-300"></span>

                        <span
                            class="text-[11px] font-bold uppercase tracking-[0.24em] text-cyan-200"
                        >
                            Welcome back
                        </span>
                    </div>


                    <h1
                        class="max-w-xl text-5xl font-extrabold leading-[1.02] tracking-[-0.045em] xl:text-6xl"
                    >
                        Manage your
                        <span
                            class="bg-gradient-to-r from-white via-cyan-100 to-indigo-200 bg-clip-text text-transparent"
                        >
                            content.
                        </span>

                        <br>

                        Stay in control.
                    </h1>


                    <p
                        class="mt-5 max-w-xl text-base leading-7 text-slate-200/75 xl:text-[17px]"
                    >
                        Access your centralized workspace to manage content,
                        monitor activity, and keep everything organized from
                        one secure dashboard.
                    </p>


                    {{-- Features --}}
                    <div class="mt-6 grid max-w-xl grid-cols-3 gap-3">

                        <div
                            class="rounded-2xl border border-white/10 bg-white/[.06] p-4 backdrop-blur-md"
                        >
                            <div
                                class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-white/10"
                            >
                                <svg
                                    class="h-4 w-4 text-cyan-200"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 12.75 11.25 15 15 9.75M12 3l7.5 3v5.25c0 4.635-3.13 8.665-7.5 9.75-4.37-1.085-7.5-5.115-7.5-9.75V6L12 3Z"
                                    />
                                </svg>
                            </div>

                            <p class="text-sm font-semibold text-white">
                                Secure
                            </p>

                            <p class="mt-1 text-xs text-white/45">
                                Protected access
                            </p>
                        </div>


                        <div
                            class="rounded-2xl border border-white/10 bg-white/[.06] p-4 backdrop-blur-md"
                        >
                            <div
                                class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-white/10"
                            >
                                <svg
                                    class="h-4 w-4 text-indigo-200"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3.75 3v18h16.5M7.5 16.5l3.75-4.5 3 2.25L19.5 7.5"
                                    />
                                </svg>
                            </div>

                            <p class="text-sm font-semibold text-white">
                                Organized
                            </p>

                            <p class="mt-1 text-xs text-white/45">
                                One dashboard
                            </p>
                        </div>


                        <div
                            class="rounded-2xl border border-white/10 bg-white/[.06] p-4 backdrop-blur-md"
                        >
                            <div
                                class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-white/10"
                            >
                                <svg
                                    class="h-4 w-4 text-violet-200"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M13 2 4.5 13h6L10 22l8.5-11h-6L13 2Z"
                                    />
                                </svg>
                            </div>

                            <p class="text-sm font-semibold text-white">
                                Efficient
                            </p>

                            <p class="mt-1 text-xs text-white/45">
                                Built for speed
                            </p>
                        </div>

                    </div>

                </div>

            </div>


            {{-- Bottom footer --}}
            <footer
                class="relative z-10 flex items-center justify-between border-t border-white/[.08] px-10 py-4 text-[11px] text-white/40 xl:px-14"
            >
                <span>
                    © {{ date('Y') }} CMS Workspace
                </span>

                <span class="flex items-center gap-2">
                    <svg
                        class="h-3.5 w-3.5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <rect x="5" y="10" width="14" height="10" rx="2"/>
                        <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                    </svg>

                    Encrypted connection
                </span>
            </footer>

        </section>


        {{-- =========================================================
             RIGHT LOGIN PANEL
        ========================================================== --}}
        <section
            class="relative flex min-h-screen min-h-[100dvh] items-center justify-center overflow-hidden bg-slate-50 px-5 py-10 sm:px-10 lg:h-full lg:min-h-0 lg:overflow-y-auto lg:px-12 xl:px-16"
        >

            {{-- Background decoration --}}
            <div
                class="pointer-events-none absolute -right-32 -top-32 h-80 w-80 rounded-full bg-indigo-100/70 blur-3xl"
            ></div>

            <div
                class="pointer-events-none absolute -bottom-32 -left-32 h-80 w-80 rounded-full bg-cyan-100/50 blur-3xl"
            ></div>


            <div class="relative z-10 w-full max-w-[440px]">

                {{-- Mobile logo --}}
                <div class="mb-10 flex items-center gap-3 lg:hidden">

                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white p-2 shadow-sm ring-1 ring-slate-200"
                    >
                        <img
                            src="{{ asset('images/cms-logo.png') }}"
                            alt="CMS Workspace logo"
                            class="h-full w-full object-contain"
                        >
                    </div>

                    <div>
                        <p class="font-bold tracking-tight text-slate-900">
                            CMS Workspace
                        </p>

                        <p class="text-xs text-slate-400">
                            Content Management System
                        </p>
                    </div>

                </div>


                {{-- Login card --}}
                <div
                    class="rounded-[28px] border border-slate-200/80 bg-white p-6 shadow-[0_20px_60px_-15px_rgba(15,23,42,0.12)] sm:p-9"
                >

                    {{-- Heading --}}
                    <div class="mb-8">

                        <p
                            class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-indigo-600"
                        >
                            Admin portal
                        </p>

                        <h2
                            class="text-[30px] font-bold tracking-[-0.035em] text-slate-950 sm:text-[34px]"
                        >
                            Welcome back
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Enter your credentials to access the CMS dashboard.
                        </p>

                    </div>


                    {{-- Validation error --}}
                    @if ($errors->any())
                        <div id="login-error-box" class="mb-6">
                            <x-alert type="error">
                                {{ $errors->first() }}
                            </x-alert>
                        </div>
                    @endif

                    @if (session('lockout_seconds'))
                        <div id="lockout-notice" data-seconds="{{ (int) session('lockout_seconds') }}"
                             class="mb-6 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />
                            </svg>
                            <p class="text-sm font-medium text-rose-700">
                                Too many failed attempts. You can try again in
                                <span id="lockout-count" class="font-bold">{{ (int) session('lockout_seconds') }}</span>s.
                            </p>
                        </div>
                    @endif


                    {{-- Form --}}
                    <form
                        id="login-form"
                        method="POST"
                        action="{{ route('login.attempt') }}"
                        data-turbo="false"
                        class="space-y-5"
                    >
                        @csrf


                        {{-- Email --}}
                        <div>

                            <label
                                for="email"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Email address
                            </label>

                            <div class="relative">

                                <div
                                    class="pointer-events-none absolute inset-y-0 left-0 flex w-12 items-center justify-center text-slate-400"
                                >
                                    <svg
                                        class="h-[18px] w-[18px]"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    >
                                        <rect x="3" y="5" width="18" height="14" rx="3"/>
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m4 7 8 6 8-6"
                                        />
                                    </svg>
                                </div>

                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="name@example.com"
                                    required
                                    autofocus
                                    autocomplete="email"
                                    class="login-input h-[54px] w-full rounded-xl border border-slate-200 bg-slate-50/70 pl-12 pr-4 text-[14px] text-slate-900 outline-none transition duration-200 placeholder:text-slate-400 hover:border-slate-300 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10"
                                >

                            </div>

                        </div>


                        {{-- Password --}}
                        <div>

                            <div class="mb-2 flex items-center justify-between">

                                <label
                                    for="password"
                                    class="block text-sm font-semibold text-slate-700"
                                >
                                    Password
                                </label>

                                <span class="text-[11px] font-medium text-slate-400">
                                    Case sensitive
                                </span>

                            </div>


                            <div class="relative">

                                <div
                                    class="pointer-events-none absolute inset-y-0 left-0 flex w-12 items-center justify-center text-slate-400"
                                >
                                    <svg
                                        class="h-[18px] w-[18px]"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    >
                                        <rect x="5" y="10" width="14" height="10" rx="2"/>
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M8 10V7a4 4 0 0 1 8 0v3"
                                        />
                                    </svg>
                                </div>

                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    placeholder="Enter your password"
                                    required
                                    autocomplete="current-password"
                                    class="login-input h-[54px] w-full rounded-xl border border-slate-200 bg-slate-50/70 pl-12 pr-12 text-[14px] text-slate-900 outline-none transition duration-200 placeholder:text-slate-400 hover:border-slate-300 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10"
                                >


                                <button
                                    type="button"
                                    id="toggle-login-password"
                                    class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-xl text-slate-400 outline-none transition hover:text-indigo-600 focus:text-indigo-600"
                                    aria-label="Show password"
                                >

                                    <svg
                                        id="eye-open"
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                        />
                                    </svg>


                                    <svg
                                        id="eye-closed"
                                        class="hidden h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 4.7A9.8 9.8 0 0 1 12 4.5c4.64 0 8.57 3.01 9.96 7.18.07.21.07.43 0 .64a10.8 10.8 0 0 1-2.22 3.77M6.61 6.61a10.8 10.8 0 0 0-4.57 5.07c-.07.21-.07.43 0 .64C3.42 16.49 7.36 19.5 12 19.5a9.8 9.8 0 0 0 4.1-.89"
                                        />
                                    </svg>

                                </button>

                            </div>

                        </div>


                        {{-- Options --}}
                        <div class="flex items-center justify-between pt-1">

                            <label
                                for="remember"
                                class="group flex cursor-pointer items-center gap-2.5"
                            >
                                <input
                                    id="remember"
                                    type="checkbox"
                                    name="remember"
                                    value="1"
                                    class="h-4 w-4 cursor-pointer rounded border-slate-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500/30"
                                >

                                <span
                                    class="text-sm text-slate-500 transition group-hover:text-slate-700"
                                >
                                    Remember me
                                </span>
                            </label>


                            <div
                                class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400"
                            >
                                <svg
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 3 4.5 6v5.25c0 4.635 3.13 8.665 7.5 9.75 4.37-1.085 7.5-5.115 7.5-9.75V6L12 3Z"
                                    />
                                </svg>

                                Authorized users only
                            </div>

                        </div>


                        {{-- Submit --}}
                        <button
                            id="login-submit"
                            type="submit"
                            class="group relative mt-1 inline-flex h-[54px] w-full items-center justify-center overflow-hidden rounded-xl bg-slate-950 px-5 text-sm font-semibold text-white shadow-lg shadow-slate-950/15 outline-none transition duration-200 hover:-translate-y-0.5 hover:bg-indigo-600 hover:shadow-xl hover:shadow-indigo-600/20 focus:ring-4 focus:ring-indigo-500/20 active:translate-y-0"
                        >

                            <span
                                id="login-button-text"
                                class="flex items-center gap-2"
                            >
                                Sign in to CMS

                                <svg
                                    class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                                    />
                                </svg>
                            </span>


                            <span
                                id="login-loading"
                                class="hidden items-center gap-2"
                            >
                                <svg
                                    class="h-4 w-4 animate-spin"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="9"
                                        stroke="currentColor"
                                        stroke-width="3"
                                    />

                                    <path
                                        class="opacity-90"
                                        fill="currentColor"
                                        d="M21 12a9 9 0 0 0-9-9v3a6 6 0 0 1 6 6h3Z"
                                    />
                                </svg>

                                Signing in...
                            </span>

                        </button>

                    </form>


                    {{-- Security message --}}
                    <div
                        class="mt-7 flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3"
                    >

                        <div
                            class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                        >
                            <svg
                                class="h-3.5 w-3.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 12.75 11.25 15 15 9.75M12 3l7.5 3v5.25c0 4.635-3.13 8.665-7.5 9.75-4.37-1.085-7.5-5.115-7.5-9.75V6L12 3Z"
                                />
                            </svg>
                        </div>

                        <p class="text-[11px] leading-5 text-slate-400">
                            This is a protected administrative workspace.
                            Your session and authentication activity may be
                            securely logged.
                        </p>

                    </div>

                </div>


                {{-- Bottom text --}}
                <div
                    class="mt-6 flex items-center justify-center gap-2 text-center text-[11px] text-slate-400"
                >
                    <span>
                        © {{ date('Y') }} CMS
                    </span>

                    <span class="text-slate-300">•</span>

                    <span>
                        Secure Administration Portal
                    </span>
                </div>

            </div>

        </section>

    </div>

</main>


@include('shared.notifications')


<script>
    (() => {
        const password = document.getElementById('password');
        const toggle = document.getElementById('toggle-login-password');

        const eyeOpen = document.getElementById('eye-open');
        const eyeClosed = document.getElementById('eye-closed');

        toggle?.addEventListener('click', () => {
            const showingPassword = password.type === 'password';

            password.type = showingPassword ? 'text' : 'password';

            eyeOpen?.classList.toggle('hidden', showingPassword);
            eyeClosed?.classList.toggle('hidden', !showingPassword);

            toggle.setAttribute(
                'aria-label',
                showingPassword ? 'Hide password' : 'Show password'
            );
        });


        /*
         * Loading state after valid form submission.
         * This prevents accidental double clicks while the request
         * is being processed.
         */
        const form = document.querySelector(
            'form[action="{{ route('login.attempt') }}"]'
        );

        const submitButton = document.getElementById('login-submit');
        const buttonText = document.getElementById('login-button-text');
        const loading = document.getElementById('login-loading');

        form?.addEventListener('submit', () => {
            if (!form.checkValidity()) {
                return;
            }

            submitButton.disabled = true;
            submitButton.classList.add(
                'cursor-not-allowed',
                'opacity-80'
            );

            buttonText?.classList.add('hidden');

            loading?.classList.remove('hidden');
            loading?.classList.add('flex');
        });


        /*
         * Lockout countdown after failed attempts.
         * Email, password and submit stay disabled until it reaches zero.
         */
        const lockoutNotice = document.getElementById('lockout-notice');

        if (lockoutNotice) {
            const loginForm = document.getElementById('login-form');
            const lockoutFields = loginForm
                ? loginForm.querySelectorAll('input, button[type="submit"]')
                : [];

            const lockoutCount = document.getElementById('lockout-count');

            let remaining = parseInt(lockoutNotice.dataset.seconds, 10) || 0;

            lockoutFields.forEach((field) => {
                field.disabled = true;
            });

            if (lockoutCount) {
                lockoutCount.textContent = remaining;
            }

            const lockoutTimer = setInterval(() => {
                remaining -= 1;

                if (remaining <= 0) {
                    clearInterval(lockoutTimer);
                    lockoutNotice.remove();
                    document.getElementById('login-error-box')?.remove();

                    lockoutFields.forEach((field) => {
                        field.disabled = false;
                    });

                    return;
                }

                if (lockoutCount) {
                    lockoutCount.textContent = remaining;
                }
            }, 1000);
        }
    })();
</script>

</body>
</html>
