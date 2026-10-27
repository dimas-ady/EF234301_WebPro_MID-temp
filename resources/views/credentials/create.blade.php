<x-layouts.app title="Add Credential">
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('vault') }}" class="text-sm font-medium text-violet-700 hover:text-violet-800">&larr; Back to Vault</a>
        <div class="mt-5 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="text-2xl font-semibold tracking-tight">Add credential</h2>
            <p class="mt-2 text-sm text-stone-600">Securely save a new service login in your Vault.</p>
            <x-form-errors />
            <form method="POST" action="{{ route('vault.credentials.store') }}" class="mt-8 space-y-5">
                @csrf
                @include('credentials.partials.form')
                <div class="flex flex-col-reverse gap-3 pt-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('vault') }}" class="rounded-xl border border-stone-200 px-4 py-3 text-center text-sm font-semibold text-stone-600 hover:bg-stone-50">Cancel</a>
                    <button type="submit" class="rounded-xl bg-violet-700 px-4 py-3 text-sm font-semibold text-white hover:bg-violet-800">Save credential</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
