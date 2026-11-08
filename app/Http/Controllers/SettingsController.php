<?php

namespace App\Http\Controllers;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Fortify\Features;

class SettingsController extends Controller
{
    use PasswordValidationRules;
    use ProfileValidationRules;

    public function profile(): View
    {
        return view('pages.settings.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate($this->profileRules($user->id));
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('status', __('Profile updated.'));
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return to_route('dashboard');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    public function appearance(): View
    {
        return view('pages.settings.appearance');
    }

    public function security(): View
    {
        $user = Auth::user();
        $requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');

        return view('pages.settings.security', [
            'canManageTwoFactor' => Features::canManageTwoFactorAuthentication(),
            'twoFactorEnabled' => $user->hasEnabledTwoFactorAuthentication(),
            'twoFactorPending' => $requiresConfirmation && $user->two_factor_secret && ! $user->two_factor_confirmed_at,
            'requiresConfirmation' => $requiresConfirmation,
            'canManagePasskeys' => Features::canManagePasskeys(),
            'passkeys' => Features::canManagePasskeys()
                ? $user->passkeys()->latest()->get()
                : collect(),
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => $this->currentPasswordRules(),
            'password' => $this->passwordRules(),
        ]);
        $request->user()->update(['password' => $validated['password']]);

        return back()->with('status', __('Password updated.'));
    }

    public function deleteAccount(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);
        $user = $request->user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('home');
    }
}
