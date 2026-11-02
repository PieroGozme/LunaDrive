<?php

use App\Http\Controllers\Drive\FileController;
use App\Http\Controllers\Drive\FileManagerController;
use App\Http\Controllers\Drive\FolderController;
use App\Http\Controllers\Drive\QuotaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\SettingsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('files.index')
        : Inertia::render('Welcome');
});

Route::get('/auth/google', [SettingsController::class, 'googleLogin'])->name('auth.google');
Route::get('/connected-accounts/google/callback', [SettingsController::class, 'googleCallback'])
    ->name('google.callback');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('files.index'))->name('dashboard');

    Route::get('/files', [FileManagerController::class, 'index'])->name('files.index');
    Route::post('/files', [FileController::class, 'store'])->name('files.store');
    Route::patch('/files/{file}', [FileController::class, 'update'])->name('files.update');
    Route::delete('/files/{file}', [FileController::class, 'destroy'])->name('files.destroy');
    Route::get('/files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::get('/files/{file}/preview', [FileController::class, 'preview'])->name('files.preview');

    Route::post('/folders', [FolderController::class, 'store'])->name('folders.store');
    Route::patch('/folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
    Route::delete('/folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');

    Route::get('/quota', [QuotaController::class, 'index'])->name('quota.index');
    Route::patch('/quota/policy', [QuotaController::class, 'updatePolicy'])->name('quota.policy');
    Route::post('/quota/sync', [QuotaController::class, 'sync'])->name('quota.sync');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/google', [SettingsController::class, 'saveGoogleConfig'])->name('settings.google');
    Route::get('/settings/google/connect', [SettingsController::class, 'connectGoogle'])->name('settings.google.connect');
    Route::delete('/settings/accounts/{account}', [SettingsController::class, 'disconnectAccount'])->name('settings.accounts.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
