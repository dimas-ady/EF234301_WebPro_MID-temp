<x-layouts.guest title="Sign in - Vaultify">
    <x-auth-card>
        <div class="mb-8">
            <h1 class="text-2xl font-semibold">Sign in to Vaultify</h1>
            <p class="mt-2 text-sm text-stone-600">Manage your digital security with confidence.</p>
        </div>
        <x-form-errors />
        <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="mb-2 block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="w-full rounded-lg border border-stone-300 px-3 py-2.5 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-100">
            </div>
            <div>
                <label for="password" class="mb-2 block text-sm font-medium">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password" class="w-full rounded-lg border border-stone-300 px-3 py-2.5 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-100">
            </div>
            <label class="flex items-center gap-2 text-sm text-stone-600">
                <input type="checkbox" name="remember" class="rounded border-stone-300 text-violet-600 focus:ring-violet-500">
                Remember me
            </label>
            <button type="submit" class="w-full rounded-lg bg-violet-700 px-4 py-3 font-medium text-white transition hover:bg-violet-800">Sign in</button>
        </form>
        <p class="mt-6 text-center text-sm text-stone-600">Don't have an account?
            <a href="{{ route('register') }}" class="font-medium text-violet-700 hover:text-violet-800">Register</a>
        </p>
    </x-auth-card>
</x-layouts.guest>
