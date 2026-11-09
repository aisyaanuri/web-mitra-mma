<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_without_two_factor_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Benar-123!'),
        ]);

        $this->assertTrue(Hash::check('Benar-123!', $user->password));

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'salah-total',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_with_two_factor_cannot_start_challenge_with_wrong_password(): void
    {
        $user = User::factory()->withTwoFactor()->create([
            'password' => Hash::make('Benar-123!'),
        ]);

        $this->assertTrue(Hash::check('Benar-123!', $user->password));

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'salah-total',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_with_expired_lock_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Benar-123!'),
            'failed_login_attempts' => config('security.login.max_attempts'),
            'locked_until' => now()->subSecond(),
        ]);

        $this->assertTrue(Hash::check('Benar-123!', $user->password));

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'salah-total',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
