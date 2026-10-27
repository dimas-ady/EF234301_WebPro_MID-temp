<x-layouts.app title="Password Generator">
    <div class="mx-auto max-w-4xl space-y-8">
        <section>
            <p class="text-sm font-medium text-violet-700">Create stronger access</p>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight text-stone-900">Password Generator</h2>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-stone-600">Create a strong random password for your credentials.</p>
        </section>

        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-8" aria-label="Password generator settings" data-password-generator>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <span id="generated-password-label" class="sr-only">Generated password</span>
                <output id="generated-password" aria-labelledby="generated-password-label" aria-live="polite" class="flex min-h-14 min-w-0 flex-1 items-center overflow-x-auto rounded-xl border border-stone-200 bg-stone-50 px-4 py-3.5 font-mono text-lg tracking-widest text-stone-400">No password generated</output>
                <button type="button" data-copy-generated-password disabled class="rounded-xl border border-stone-200 px-4 py-3.5 text-sm font-semibold text-stone-400 transition hover:border-violet-300 hover:text-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">Copy</button>
            </div>

            <div class="mt-8 border-t border-stone-100 pt-7">
                <div class="flex items-center justify-between">
                    <label for="password-length" class="text-sm font-semibold text-stone-800">Password length</label>
                    <output data-password-length-value for="password-length" class="rounded-lg bg-violet-50 px-3 py-1 text-sm font-semibold text-violet-700">16 characters</output>
                </div>
                <input id="password-length" type="range" min="8" max="64" value="16" aria-valuemin="8" aria-valuemax="64" aria-valuenow="16" aria-describedby="password-length-help" class="mt-5 w-full accent-violet-700">
                <div class="mt-2 flex justify-between text-xs text-stone-400"><span>8</span><span>64</span></div>
                <p id="password-length-help" class="mt-2 text-xs text-stone-500">Choose a length between 8 and 64 characters.</p>
            </div>

            <fieldset class="mt-8 border-t border-stone-100 pt-7">
                <legend class="text-sm font-semibold text-stone-800">Character sets</legend>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ([
                        'uppercase' => 'Uppercase letters (A-Z)',
                        'lowercase' => 'Lowercase letters (a-z)',
                        'numbers' => 'Numbers (0-9)',
                        'symbols' => 'Symbols (!@#$)',
                    ] as $id => $label)
                        <label class="flex items-center gap-3 rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 transition hover:border-violet-300">
                            <input id="{{ $id }}" data-password-character="{{ $id }}" type="checkbox" checked class="h-4 w-4 rounded border-stone-300 text-violet-700 focus:ring-violet-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-stone-500">Symbols used: <span class="font-mono">!@#$%^&amp;*()-_=+[]{}:,.?</span></p>
            </fieldset>

            <button type="button" data-generate-password class="mt-8 w-full rounded-xl bg-violet-700 px-4 py-3.5 text-sm font-semibold text-white transition hover:bg-violet-800 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2">Generate password</button>
            <p data-password-generator-status role="status" aria-live="polite" class="mt-3 min-h-5 text-sm text-stone-500"></p>
        </section>

        <p class="text-xs text-stone-400">Passwords are generated entirely in your browser and are not stored or sent to the server.</p>
    </div>
</x-layouts.app>
