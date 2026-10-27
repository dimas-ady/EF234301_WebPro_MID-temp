<x-layouts.guest title="Register - Vaultify">
    <x-auth-card>
        <div class="mb-8">
            <h1 class="text-2xl font-semibold">Create your Vaultify account</h1>
            <p class="mt-2 text-sm text-stone-600">Start building a safer digital space.</p>
        </div>
        <x-form-errors />
        <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
            @csrf
            <div>
                <label for="name" class="mb-2 block text-sm font-medium">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" class="w-full rounded-lg border border-stone-300 px-3 py-2.5 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-100">
            </div>
            <div>
                <label for="email" class="mb-2 block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="w-full rounded-lg border border-stone-300 px-3 py-2.5 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-100">
            </div>
            <div>
                <label for="password" class="mb-2 block text-sm font-medium">Password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" class="w-full rounded-lg border border-stone-300 px-3 py-2.5 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-100">
            </div>
            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-medium">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="w-full rounded-lg border border-stone-300 px-3 py-2.5 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-100">
            </div>
            <button type="submit" class="w-full rounded-lg bg-violet-700 px-4 py-3 font-medium text-white transition hover:bg-violet-800">Create account</button>
        </form>
        <p class="mt-6 text-center text-sm text-stone-600">Already have an account?
            <a href="{{ route('login') }}" class="font-medium text-violet-700 hover:text-violet-800">Sign in</a>
        </p>
    </x-auth-card>
</x-layouts.guest>
