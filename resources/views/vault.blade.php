<x-layouts.app title="Vault">
    <div class="mx-auto max-w-7xl space-y-8">
        <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-medium text-violet-700">Your credential collection</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-stone-900">Vault</h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-stone-600">Store and manage access to your digital services from one secure space.</p>
            </div>
            <a href="{{ route('vault.credentials.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-violet-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-violet-800 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2">
                <span class="text-lg leading-none" aria-hidden="true">+</span>
                Add credential
            </a>
        </section>

        <section class="space-y-4" aria-label="Vault search and filters">
            <form method="GET" action="{{ route('vault') }}" class="flex max-w-2xl flex-col gap-3 sm:flex-row">
                <label for="credential-search" class="sr-only">Search credentials</label>
                <div class="relative flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-stone-400" aria-hidden="true">&#9906;</span>
                    <input id="credential-search" name="search" type="search" value="{{ $search }}" maxlength="100" placeholder="Search services, usernames, or URLs..." class="w-full rounded-xl border border-stone-200 bg-white px-11 py-3 text-sm text-stone-900 outline-none transition placeholder:text-stone-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-100">
                </div>
                <input type="hidden" name="filter" value="{{ $filter }}">
                <button type="submit" class="rounded-xl bg-stone-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-stone-800">Search</button>
                @if ($search !== '')
                    <a href="{{ route('vault', ['filter' => $filter]) }}" class="rounded-xl border border-stone-200 bg-white px-5 py-3 text-center text-sm font-medium text-stone-600 transition hover:border-violet-300 hover:text-violet-700">Clear search</a>
                @endif
            </form>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('vault', array_filter(['search' => $search, 'filter' => 'all'])) }}" @class([
                    'rounded-lg px-4 py-2 text-sm font-medium transition',
                    'bg-stone-900 text-white' => $filter === 'all',
                    'border border-stone-200 bg-white text-stone-600 hover:border-violet-300 hover:text-violet-700' => $filter !== 'all',
                ])>All</a>
                <a href="{{ route('vault', array_filter(['search' => $search, 'filter' => 'favorites'])) }}" @class([
                    'rounded-lg px-4 py-2 text-sm font-medium transition',
                    'bg-stone-900 text-white' => $filter === 'favorites',
                    'border border-stone-200 bg-white text-stone-600 hover:border-violet-300 hover:text-violet-700' => $filter !== 'favorites',
                ])>Favorites</a>
            </div>
        </section>

        @if ($credentials->isEmpty())
        <section class="rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center sm:px-12" aria-labelledby="empty-vault-title">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-50 text-2xl text-violet-700" aria-hidden="true">&#9671;</div>
            <h3 id="empty-vault-title" class="mt-5 text-lg font-semibold text-stone-900">{{ $search !== '' ? 'No credentials found' : ($filter === 'favorites' ? 'No favorites yet' : 'No credentials yet') }}</h3>
            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-stone-500">
                @if ($search !== '')
                    No credentials match "{{ $search }}".
                @elseif ($filter === 'favorites')
                    Credentials you mark as favorites will appear here.
                @else
                    Credentials you save will appear here. Start by adding your first service login.
                @endif
            </p>
            @if ($search === '' && $filter === 'all')
            <a href="{{ route('vault.credentials.create') }}" class="mt-6 inline-flex rounded-xl border border-violet-200 px-4 py-2.5 text-sm font-semibold text-violet-700 transition hover:bg-violet-50 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2">Add your first credential</a>
            @endif
        </section>
        @else
        <p class="rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-xs leading-5 text-stone-600">
            This check only compares passwords with the available local wordlist. A result does not prove that an account has actually been compromised.
        </p>
        <section class="grid gap-4 md:grid-cols-2" aria-label="Credential list">
            @foreach ($credentials as $credential)
                <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm" data-credential-card>
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h3 class="truncate text-lg font-semibold text-stone-900">{{ $credential->title }}</h3>
                            @if ($credential->website_url)
                                <a href="{{ $credential->website_url }}" target="_blank" rel="noopener noreferrer" class="mt-1 block truncate text-sm text-violet-700 hover:text-violet-800">{{ $credential->website_url }}</a>
                            @endif
                        </div>
                        <button type="button" data-toggle-favorite="{{ route('vault.credentials.favorite', $credential) }}" aria-pressed="{{ $credential->is_favorite ? 'true' : 'false' }}" aria-label="{{ $credential->is_favorite ? 'Remove from favorites' : 'Add to favorites' }}" class="rounded-lg px-2.5 py-1 text-xs font-medium transition {{ $credential->is_favorite ? 'bg-amber-50 text-amber-600' : 'bg-stone-50 text-stone-500 hover:text-amber-600' }}">
                            <span data-favorite-icon aria-hidden="true">{{ $credential->is_favorite ? '★' : '☆' }}</span>
                            <span data-favorite-label>{{ $credential->is_favorite ? 'Favorite' : 'Add favorite' }}</span>
                        </button>
                    </div>
                    <dl class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-stone-500">Username</dt>
                            <dd class="flex max-w-[65%] items-center gap-2 truncate text-right text-stone-800"><span data-username-value class="truncate">{{ $credential->username ?: 'Not provided' }}</span><button type="button" data-copy-username aria-label="Copy username" class="shrink-0 text-xs font-medium text-violet-700 hover:text-violet-800">Copy</button></dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-stone-500">Password</dt>
                            <dd class="flex items-center gap-2"><span data-password-value class="font-mono tracking-widest text-stone-700" aria-label="Masked password">••••••••</span><button type="button" data-reveal-password="{{ route('vault.credentials.password', $credential) }}" aria-pressed="false" class="text-xs font-medium text-violet-700 hover:text-violet-800">Reveal</button><button type="button" data-copy-password="{{ route('vault.credentials.password', $credential) }}" class="text-xs font-medium text-violet-700 hover:text-violet-800">Copy</button></dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-stone-500">Local wordlist</dt>
                            <dd class="text-right text-xs font-medium {{ $credential->wordlist_match === true ? 'text-amber-700' : ($credential->wordlist_match === false ? 'text-emerald-700' : 'text-stone-500') }}">
                                @if ($credential->wordlist_match === true)
                                    Found in local wordlist
                                @elseif ($credential->wordlist_match === false)
                                    Not found in local wordlist
                                @else
                                    Not checked
                                @endif
                            </dd>
                        </div>
                    </dl>
                    <p data-credential-message class="mt-3 min-h-4 text-xs text-stone-500" aria-live="polite"></p>
                    <div class="mt-2 flex items-center justify-end gap-2 border-t border-stone-100 pt-4">
                        <a href="{{ route('vault.credentials.edit', $credential) }}" class="rounded-lg border border-stone-200 px-3 py-2 text-sm font-medium text-stone-600 transition hover:border-violet-300 hover:text-violet-700">Edit</a>
                        <form method="POST" action="{{ route('vault.credentials.destroy', $credential) }}" onsubmit="return confirm('Delete this credential?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50">Delete</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </section>
        @endif

        @if (session('status'))
            <p class="rounded-xl border border-violet-200 bg-violet-50 px-4 py-3 text-sm text-violet-800" role="status">{{ session('status') }}</p>
        @endif
    </div>
</x-layouts.app>
    @if ($isEdit)
        <p class="mt-2 text-xs text-stone-500">Leave blank to keep the current password.</p>
