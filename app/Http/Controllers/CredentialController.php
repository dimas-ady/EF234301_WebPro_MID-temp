<?php

namespace App\Http\Controllers;

use App\Models\Credential;
use App\Services\PasswordWordlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CredentialController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'filter' => ['nullable', Rule::in(['all', 'favorites'])],
        ], [
            'search.string' => 'Search must be text.',
            'search.max' => 'Search may not exceed 100 characters.',
            'filter.in' => 'Invalid filter.',
        ]);

        $search = $validated['search'] ?? '';
        $filter = $validated['filter'] ?? 'all';
        $credentials = $request->user()->credentials()
            ->when($filter === 'favorites', fn ($query) => $query->where('is_favorite', true))
            ->when($search !== '', function ($query) use ($search): void {
                $term = '%'.$search.'%';
                $query->where(function ($query) use ($term): void {
                    $query->where('title', 'like', $term)
                        ->orWhere('username', 'like', $term)
                        ->orWhere('website_url', 'like', $term);
                });
            })
            ->latest()
            ->get();

        return view('vault', compact('credentials', 'search', 'filter'));
    }

    public function create(): View
    {
        return view('credentials.create');
    }

    public function store(Request $request, PasswordWordlistService $wordlistService): RedirectResponse
    {
        $validated = $this->validateCredential($request, true);

        $password = $validated['password'];
        $request->user()->credentials()->create([
            'title' => $validated['title'],
            'website_url' => $validated['website_url'] ?? null,
            'username' => $validated['username'] ?? null,
            'password_encrypted' => Crypt::encryptString($password),
            'notes_encrypted' => filled($validated['notes'] ?? null)
                ? Crypt::encryptString($validated['notes'])
                : null,
            'wordlist_match' => $wordlistService->check($password),
            'wordlist_checked_at' => $wordlistService->isAvailable() ? now() : null,
        ]);

        return redirect()->route('vault')->with('status', 'Credential added successfully.');
    }

    public function edit(Request $request, int $credential): View
    {
        $credentialModel = $this->ownedCredential($request, $credential);

        return view('credentials.edit', [
            'credential' => $credentialModel,
            'notes' => $credentialModel->notes_encrypted === null
                ? ''
                : Crypt::decryptString($credentialModel->notes_encrypted),
        ]);
    }

    public function update(Request $request, int $credential, PasswordWordlistService $wordlistService): RedirectResponse
    {
        $credentialModel = $this->ownedCredential($request, $credential);
        $validated = $this->validateCredential($request, false);

        $updates = [
            'title' => $validated['title'],
            'website_url' => $validated['website_url'] ?? null,
            'username' => $validated['username'] ?? null,
            'notes_encrypted' => filled($validated['notes'] ?? null)
                ? Crypt::encryptString($validated['notes'])
                : null,
        ];

        if (filled($validated['password'] ?? null)) {
            $password = $validated['password'];
            $updates['password_encrypted'] = Crypt::encryptString($password);
            $updates['wordlist_match'] = $wordlistService->check($password);
            $updates['wordlist_checked_at'] = $wordlistService->isAvailable() ? now() : null;
        }

        $credentialModel->update($updates);

        return redirect()->route('vault')->with('status', 'Credential updated successfully.');
    }

    public function destroy(Request $request, int $credential): RedirectResponse
    {
        $this->ownedCredential($request, $credential)->delete();

        return redirect()->route('vault')->with('status', 'Credential deleted successfully.');
    }

    public function toggleFavorite(Request $request, int $credential): JsonResponse
    {
        $credentialModel = $this->ownedCredential($request, $credential);
        $credentialModel->update(['is_favorite' => ! $credentialModel->is_favorite]);

        return response()->json([
            'is_favorite' => $credentialModel->is_favorite,
            'message' => $credentialModel->is_favorite
                ? 'Credential added to favorites.'
                : 'Credential removed from favorites.',
        ]);
    }

    public function revealPassword(Request $request, int $credential)
    {
        $credentialModel = $this->ownedCredential($request, $credential);

        return response()
            ->json(['password' => Crypt::decryptString($credentialModel->password_encrypted)])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    private function ownedCredential(Request $request, int $credential): Credential
    {
        return $request->user()->credentials()->findOrFail($credential);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCredential(Request $request, bool $passwordRequired): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:2048'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => [$passwordRequired ? 'required' : 'nullable', 'string', 'max:10000'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ], [
            'title.required' => 'Service name is required.',
            'title.string' => 'Service name must be text.',
            'title.max' => 'Service name may not exceed 255 characters.',
            'website_url.url' => 'Enter a valid website URL.',
            'website_url.max' => 'Website URL is too long.',
            'username.string' => 'Username must be text.',
            'username.max' => 'Username may not exceed 255 characters.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be text.',
            'password.max' => 'Password is too long.',
            'notes.string' => 'Notes must be text.',
            'notes.max' => 'Notes are too long.',
        ]);
    }
}
