<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ShortUrlController;
use Illuminate\Support\Facades\Route;

// Root -> dashboard (the dashboard route itself is auth-protected, so this
// effectively kicks you to /login when not signed in).
Route::get('/', fn () => redirect('/dashboard'));

// --- auth ---
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- invitation accept (public; the token IS the auth) ---
Route::get('/invitations/accept/{token}',  [InvitationController::class, 'showAccept'])->name('invitations.accept.show');
Route::post('/invitations/accept/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');

// --- everything behind a real session ---
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    // Companies (SuperAdmin only -- enforced inside the controller).
    Route::get('/companies',                [CompanyController::class, 'index'])->name('companies.index');
    Route::post('/companies',               [CompanyController::class, 'store'])->name('companies.store');
    Route::put('/companies/{company}',      [CompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{company}',   [CompanyController::class, 'destroy'])->name('companies.destroy');

    // Invitations.
    Route::get('/invitations',  [InvitationController::class, 'index'])->name('invitations.index');
    Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');

    // Short URLs.
    Route::get('/short-urls',            [ShortUrlController::class, 'index'])->name('short-urls.index');
    Route::post('/short-urls',           [ShortUrlController::class, 'store'])->name('short-urls.store');
    Route::delete('/short-urls/{id}',    [ShortUrlController::class, 'destroy'])->name('short-urls.destroy');
});

// Public short-URL resolver. Per spec: redirects to the original URL.
Route::get('/s/{code}', [ShortUrlController::class, 'resolve']);
