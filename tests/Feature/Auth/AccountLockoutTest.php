<?php

namespace Tests\Feature\Auth;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountLockoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_locks_after_reaching_max_failed_attempts(): void
    {
        $user = User::factory()->create();
        $maxAttempts = config('security.login.max_attempts');

        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $user->refresh();

        $this->assertSame($maxAttempts, $user->failed_login_attempts);
        $this->assertTrue($user->isLocked());
        $this->assertSame($maxAttempts, LoginAttempt::where('email', $user->email)->where('successful', false)->count());
    }

    public function test_locked_account_cannot_login_even_with_correct_password(): void
    {
        $user = User::factory()->create([
            'failed_login_attempts' => config('security.login.max_attempts'),
            'locked_until' => now()->addMinutes(15),
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrorsIn('email');
        $this->assertGuest();
    }

    public function test_successful_login_resets_failed_attempts(): void
    {
        $user = User::factory()->create([
            'failed_login_attempts' => 2,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticated();

        $user->refresh();
        $this->assertSame(0, $user->failed_login_attempts);
        $this->assertNotNull($user->last_login_at);
        $this->assertTrue(LoginAttempt::where('email', $user->email)->where('successful', true)->exists());
    }

    public function test_inactive_account_cannot_login(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrorsIn('email');
        $this->assertGuest();
    }
}
