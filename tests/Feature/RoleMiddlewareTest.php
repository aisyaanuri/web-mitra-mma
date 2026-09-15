<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'role:owner'])
            ->get('/__test/owner-only', fn () => 'ok');
    }

    public function test_user_with_allowed_role_can_access_route(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($owner)
            ->get('/__test/owner-only')
            ->assertOk();
    }

    public function test_user_without_allowed_role_is_forbidden(): void
    {
        $gudang = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($gudang)
            ->get('/__test/owner-only')
            ->assertForbidden();
    }

    public function test_guest_cannot_access_role_protected_route(): void
    {
        $this->get('/__test/owner-only')->assertRedirect(route('login'));
    }
}
