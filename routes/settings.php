<?php

use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', [SettingsController::class, 'profile'])->name('profile.edit');
    Route::patch('settings/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::post('settings/profile/verification-notification', [SettingsController::class, 'resendVerification'])->name('verification.send');
    Route::delete('settings/profile', [SettingsController::class, 'deleteAccount'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('settings/appearance', [SettingsController::class, 'appearance'])->name('appearance.edit');
    Route::get('settings/security', [SettingsController::class, 'security'])
        ->middleware('password.confirm')
        ->name('security.edit');

    Route::put('settings/security/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
});

Route::get('.well-known/passkey-endpoints', function () {
    return response()->json([
        'enroll' => route('security.edit'),
        'manage' => route('security.edit'),
    ]);
})->name('well-known.passkeys');
