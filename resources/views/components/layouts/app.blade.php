<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Vaultify' }} · Vaultify</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
    <div id="app-shell" class="min-h-screen">
        <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-stone-950/30 lg:hidden" data-sidebar-backdrop></div>
        <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-stone-200 bg-white transition-transform duration-200 lg:translate-x-0" data-sidebar>
            <div class="flex h-20 items-center justify-between border-b border-stone-200 px-6">
                <a href="{{ route('vault') }}" class="flex items-center gap-3 text-lg font-semibold tracking-tight text-stone-900">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-700 text-sm font-bold text-white">V</span>
                    Vaultify
                </a>
                <button type="button" class="rounded-lg p-2 text-stone-500 hover:bg-stone-100 lg:hidden" aria-label="Close navigation" data-sidebar-close>
                    <span aria-hidden="true" class="text-xl leading-none">&times;</span>
                </button>
            </div>
            <div class="flex-1 px-4 py-6">
                <p class="px-3 text-xs font-semibold uppercase tracking-wider text-stone-400">Main menu</p>
                <nav class="mt-3 space-y-1" aria-label="Main navigation">
                    <a href="{{ route('vault') }}" @class([
                        'flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition',
                        'bg-violet-50 text-violet-800' => request()->routeIs('vault'),
                        'text-stone-600 hover:bg-stone-100 hover:text-stone-900' => ! request()->routeIs('vault'),
                    ]) @if (request()->routeIs('vault')) aria-current="page" @endif>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-violet-700 shadow-sm" aria-hidden="true">&#9643;</span>
                        Vault
                    </a>
                    <a href="{{ route('password.generator') }}" @class([
                        'flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition',
                        'bg-violet-50 text-violet-800' => request()->routeIs('password.generator'),
                        'text-stone-600 hover:bg-stone-100 hover:text-stone-900' => ! request()->routeIs('password.generator'),
                    ]) @if (request()->routeIs('password.generator')) aria-current="page" @endif>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-violet-700 shadow-sm" aria-hidden="true">&#9670;</span>
                        Password Generator
                    </a>
                </nav>
            </div>
            <div class="border-t border-stone-200 p-4">
                <div class="mb-3 flex items-center gap-3 rounded-xl bg-stone-50 px-3 py-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-violet-100 text-sm font-semibold text-violet-800">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-stone-800">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-stone-500">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">
                        <span aria-hidden="true">&#8594;</span>
                        Log out
                    </button>
                </form>
            </div>
        </aside>
        <div class="lg:pl-72">
            <header class="sticky top-0 z-20 flex h-20 items-center justify-between border-b border-stone-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-10">
                <div class="flex items-center gap-3">
                    <button type="button" class="rounded-xl border border-stone-200 p-2.5 text-stone-600 hover:bg-stone-50 lg:hidden" aria-label="Open navigation" data-sidebar-open>
                        <span aria-hidden="true" class="text-lg">&#9776;</span>
                    </button>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-violet-700">Your secure space</p>
                        <h1 class="text-lg font-semibold text-stone-900 sm:text-xl">{{ $title ?? 'Vault' }}</h1>
                    </div>
                </div>
            </header>
            <main class="min-h-[calc(100vh-5rem)] px-4 py-8 sm:px-6 lg:px-10 lg:py-10">{{ $slot }}</main>
        </div>
    </div>
</body>
</html>
