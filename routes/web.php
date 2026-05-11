<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\MicrosoftAuthController;
use App\Http\Controllers\Auth\OtpPasswordController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// ── Public ─────────────────────────────────────────────────────
Route::get('/', fn() => view('welcome'))->name('home');

// ── Microsoft OAuth — NO middleware ────────────────────────────
// Outside guest+auth groups so it works from login, register AND dashboard
Route::get('/auth/microsoft',          [MicrosoftAuthController::class, 'redirect'])->name('auth.microsoft');
Route::get('/auth/microsoft/callback', [MicrosoftAuthController::class, 'callback'])->name('auth.microsoft.callback');

// ── Guest only ─────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/register',  [RegisteredUserController::class,       'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class,       'store'])->name('register.store');
    Route::get('/login',     [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login',    [AuthenticatedSessionController::class, 'store'])->name('login.store');

    // OTP + magic link password reset
    Route::get('/forgot-password',    [OtpPasswordController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password',   [OtpPasswordController::class, 'sendReset'])->name('password.email');
    Route::get('/reset-code',         [OtpPasswordController::class, 'showOtpForm'])->name('password.otp-form');
    Route::post('/reset-code/verify', [OtpPasswordController::class, 'verifyOtp'])->name('password.verify-otp');
    Route::post('/reset-code/reset',  [OtpPasswordController::class, 'resetViaOtp'])->name('password.otp-reset');
    Route::get('/reset-link/{token}', [OtpPasswordController::class, 'showResetLink'])->name('password.reset-link');
    Route::post('/reset-link',        [OtpPasswordController::class, 'resetViaLink'])->name('password.link-reset');
});

// ── Authenticated ──────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard — today sync + historical sync
    Route::get('/dashboard',                  [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/sync',            [DashboardController::class, 'sync'])->name('dashboard.sync');
    Route::post('/dashboard/sync-historical', [DashboardController::class, 'syncHistorical'])->name('dashboard.sync-historical');

    // Other pages
    Route::get('/history',   [HistoryController::class,  'index'])->name('history');
    Route::get('/settings',  [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Profile
    Route::get('/profile',             [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile/info',      [ProfileController::class, 'updateInfo'])->name('profile.update-info');
    Route::patch('/profile/email',     [ProfileController::class, 'updateEmail'])->name('profile.update-email');
    Route::patch('/profile/password',  [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::delete('/profile/outlook',  [ProfileController::class, 'disconnectOutlook'])->name('profile.disconnect-outlook');
    Route::delete('/profile/data',     [ProfileController::class, 'deleteData'])->name('profile.delete-data');
    Route::delete('/profile/account',  [ProfileController::class, 'deleteAccount'])->name('profile.delete-account');
});