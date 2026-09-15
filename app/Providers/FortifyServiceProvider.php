<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
        $this->configureAuthentication();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Configure credential checking, account lockout (SR-03) and login
     * attempt auditing (SR-27, SR-28).
     */
    private function configureAuthentication(): void
    {
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if ($user && $user->isLocked()) {
                LoginAttempt::create([
                    'email' => $request->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'successful' => false,
                ]);

                throw ValidationException::withMessages([
                    Fortify::username() => __('Akun ini terkunci sementara karena terlalu banyak percobaan login gagal. Coba lagi dalam beberapa menit.'),
                ]);
            }

            $successful = $user
                && $user->is_active
                && Hash::check($request->password, $user->password);

            LoginAttempt::create([
                'email' => $request->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'successful' => $successful,
            ]);

            if (! $user) {
                return null;
            }

            if (! $successful) {
                $this->recordFailedAttempt($user);

                return null;
            }

            $user->forceFill([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ])->save();

            return $user;
        });
    }

    /**
     * Increment the failed-attempt counter and lock the account once it
     * reaches the configured threshold.
     */
    private function recordFailedAttempt(User $user): void
    {
        $maxAttempts = config('security.login.max_attempts');
        $attempts = $user->failed_login_attempts + 1;

        $user->forceFill([
            'failed_login_attempts' => $attempts,
            'locked_until' => $attempts >= $maxAttempts
                ? now()->addMinutes(config('security.login.lockout_minutes'))
                : $user->locked_until,
        ])->save();
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn () => view('pages::auth.login'));
        Fortify::verifyEmailView(fn () => view('pages::auth.verify-email'));
        Fortify::twoFactorChallengeView(fn () => view('pages::auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view('pages::auth.confirm-password'));
        Fortify::registerView(fn () => view('pages::auth.register'));
        Fortify::resetPasswordView(fn () => view('pages::auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('pages::auth.forgot-password'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('passkeys', function (Request $request) {
            $credentialId = $request->input('credential.id');

            return Limit::perMinute(10)->by(
                ($credentialId ?: $request->session()->getId()).'|'.$request->ip(),
            );
        });
    }
}
