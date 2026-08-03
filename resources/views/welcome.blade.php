<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="manifest" href="/manifest.json">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @filamentStyles
        <link rel="stylesheet" href="{{ asset('css/filament/filament/app.css') }}">
    </head>
    <body class="min-h-screen bg-gradient-to-b from-amber-50 to-amber-100 text-stone-900 dark:from-stone-950 dark:to-amber-950/40 dark:text-amber-50">
        <div class="mx-auto flex min-h-screen w-full max-w-5xl flex-col px-5 pb-12 pt-4 sm:px-6 lg:px-8">
            <nav class="flex flex-wrap items-center justify-between gap-3 py-4">
                <div class="flex items-center gap-2.5 text-base font-bold tracking-tight">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-amber-300 to-amber-600 shadow-lg shadow-amber-900/20">
                        <x-filament::icon icon="heroicon-o-ticket" class="h-5 w-5 text-amber-950" />
                    </span>
                    {{ config('app.name', 'Prize Bond') }}
                </div>
                <div class="flex gap-2">
                    @auth
                        <x-filament::button
                            tag="a"
                            href="{{ $dashboardUrl }}"
                            color="primary"
                            icon="heroicon-o-squares-2x2"
                        >
                            Dashboard
                        </x-filament::button>
                    @else
                        <x-filament::button
                            tag="a"
                            href="{{ $loginUrl }}"
                            color="primary"
                            outlined
                            icon="heroicon-o-arrow-right-end-on-rectangle"
                        >
                            Log in
                        </x-filament::button>
                        <x-filament::button
                            tag="a"
                            href="{{ $registerUrl }}"
                            color="primary"
                            icon="heroicon-o-user-plus"
                        >
                            Register
                        </x-filament::button>
                    @endauth
                </div>
            </nav>

            <header class="py-6 text-center sm:py-10">
                <h1 class="text-xl font-bold tracking-tight sm:text-2xl lg:text-4xl">
                    আপনার প্রাইজ বন্ড এক জায়গায় সহজে ব্যবস্থাপনা করুন
                </h1>
                <p class="mt-2 text-sm text-stone-600 dark:text-amber-100/70 sm:text-base">
                    নিচের উইজেট কার্ডে ক্লিক করে আপনার প্যানেলে প্রবেশ করুন
                </p>
            </header>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a
                    href="{{ $dashboardUrl }}"
                    class="group relative flex flex-col gap-1.5 overflow-hidden rounded-2xl bg-gradient-to-br from-amber-300 via-amber-400 to-amber-600 p-3 text-amber-950 shadow-xl shadow-amber-900/25 transition-all duration-300 hover:-translate-y-1.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-900/35 sm:gap-3 sm:p-6"
                >
                    <span class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_85%_0%,rgba(255,255,255,0.55),transparent_55%)]"></span>
                    <span class="pointer-events-none absolute inset-y-0 -left-1/2 w-1/2 -skew-x-12 bg-gradient-to-r from-transparent via-white/40 to-transparent transition-all duration-700 group-hover:left-[130%]"></span>

                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/55 shadow-inner sm:h-12 sm:w-12">
                        <x-filament::icon icon="heroicon-o-ticket" class="h-4 w-4 text-amber-900 sm:h-6 sm:w-6" />
                    </span>

                    <h2 class="text-xs font-bold sm:text-lg">Prize Bond</h2>
                    <p class="text-xs leading-snug text-amber-950/75 sm:text-sm">
                        আপনার প্রাইজ বন্ড সেট ও নম্বর যোগ করুন, দেখুন এবং ব্যবস্থাপনা করুন।
                    </p>

                    <span class="mt-auto flex items-center gap-1 text-xs font-semibold sm:text-sm">
                        প্যানেলে যান
                        <x-filament::icon icon="heroicon-o-arrow-right" class="h-3 w-3 transition-transform duration-200 group-hover:translate-x-1 sm:h-4 sm:w-4" />
                    </span>
                </a>
            </div>
        </div>

        @filamentScripts
    </body>
</html>
