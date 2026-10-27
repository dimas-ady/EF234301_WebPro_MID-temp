<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CredentialController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', fn () => redirect()->route('vault'));
    Route::get('/vault', [CredentialController::class, 'index'])->name('vault');
    Route::get('/vault/credentials/create', [CredentialController::class, 'create'])
        ->name('vault.credentials.create');
    Route::post('/vault/credentials', [CredentialController::class, 'store'])
        ->name('vault.credentials.store');
    Route::get('/vault/credentials/{credential}/edit', [CredentialController::class, 'edit'])
        ->whereNumber('credential')
        ->name('vault.credentials.edit');
    Route::put('/vault/credentials/{credential}', [CredentialController::class, 'update'])
        ->whereNumber('credential')
        ->name('vault.credentials.update');
    Route::delete('/vault/credentials/{credential}', [CredentialController::class, 'destroy'])
        ->whereNumber('credential')
        ->name('vault.credentials.destroy');
    Route::patch('/vault/credentials/{credential}/favorite', [CredentialController::class, 'toggleFavorite'])
        ->whereNumber('credential')
        ->name('vault.credentials.favorite');
    Route::post('/vault/credentials/{credential}/password', [CredentialController::class, 'revealPassword'])
        ->whereNumber('credential')
        ->name('vault.credentials.password');
    Route::get('/password-generator', fn () => view('password-generator'))
        ->name('password.generator');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
