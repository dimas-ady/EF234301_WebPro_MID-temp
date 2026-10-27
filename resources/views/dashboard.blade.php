<x-layouts.app title="Home - Vaultify">
    <div class="rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
        <p class="text-sm font-medium text-violet-700">Your secure space</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight">Welcome, {{ auth()->user()->name }}.</h1>
        <p class="mt-4 max-w-2xl text-stone-600">You are signed in to Vaultify. Your personal vault is ready.</p>
        @if (session('status'))
            <p class="mt-6 rounded-lg border border-violet-200 bg-violet-50 px-4 py-3 text-sm text-violet-800">{{ session('status') }}</p>
        @endif
    </div>
</x-layouts.app>
