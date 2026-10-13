<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'FinSafe') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-w-80 bg-stone-100 font-sans text-slate-800 antialiased">
        <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10 sm:px-6">
            <div class="absolute inset-x-0 top-0 h-72 bg-slate-800"></div>
            <div class="relative w-full max-w-md">
                <a href="{{ route('login') }}" class="mb-7 flex items-center justify-center gap-3 text-white">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-slate-800 shadow-lg">
                        <x-application-logo class="h-7 w-7 fill-current" />
                    </span>
                    <span class="text-xl font-bold tracking-tight">FinSafe</span>
                </a>
                <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-xl shadow-slate-900/10 sm:p-8">
                    {{ $slot }}
                </div>
                <p class="mt-6 text-center text-xs text-stone-500">Quản lý tài chính cá nhân, đơn giản và an tâm.</p>
            </div>
        </div>
    </body>
</html>
