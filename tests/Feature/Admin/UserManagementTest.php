<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_owner_can_access_user_management(): void
    {
        $gudang = User::factory()->role(UserRole::AdminGudang)->create();
        $marketplace = User::factory()->role(UserRole::AdminMarketplace)->create();
        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($gudang)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($marketplace)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($owner)->get(route('admin.users.index'))->assertOk();
    }

    public function test_owner_can_create_a_new_user(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();

        Livewire::actingAs($owner)
            ->test('pages::admin.users.form')
            ->set('name', 'Budi Gudang')
            ->set('email', 'budi.gudang@mustika.test')
            ->set('role', UserRole::AdminGudang->value)
            ->set('password', 'RahasiaAman123!')
            ->set('password_confirmation', 'RahasiaAman123!')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'budi.gudang@mustika.test',
            'role' => UserRole::AdminGudang->value,
        ]);

        $newUser = User::where('email', 'budi.gudang@mustika.test')->first();
        $this->assertTrue(Hash::check('RahasiaAman123!', $newUser->password));
        $this->assertNotNull($newUser->email_verified_at);
    }

    public function test_password_is_required_when_creating_a_user(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();

        Livewire::actingAs($owner)
            ->test('pages::admin.users.form')
            ->set('name', 'Tanpa Password')
            ->set('email', 'tanpa@mustika.test')
            ->set('role', UserRole::AdminGudang->value)
            ->call('save')
            ->assertHasErrors(['password']);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'sudah@mustika.test']);
        $owner = User::factory()->role(UserRole::Owner)->create();

        Livewire::actingAs($owner)
            ->test('pages::admin.users.form')
            ->set('name', 'Duplikat')
            ->set('email', 'sudah@mustika.test')
            ->set('role', UserRole::AdminGudang->value)
            ->set('password', 'RahasiaAman123!')
            ->set('password_confirmation', 'RahasiaAman123!')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_owner_can_edit_a_user_without_changing_password(): void
    {
        $user = User::factory()->role(UserRole::AdminGudang)->create(['name' => 'Nama Lama']);
        $originalPassword = $user->password;
        $owner = User::factory()->role(UserRole::Owner)->create();

        Livewire::actingAs($owner)
            ->test('pages::admin.users.form', ['user' => $user])
            ->set('name', 'Nama Baru')
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame($originalPassword, $user->password);
    }

    public function test_owner_can_deactivate_another_user(): void
    {
        $user = User::factory()->role(UserRole::AdminGudang)->create(['is_active' => true]);
        $owner = User::factory()->role(UserRole::Owner)->create();

        Livewire::actingAs($owner)
            ->test('pages::admin.users.index')
            ->call('toggleActive', $user->id);

        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_owner_cannot_deactivate_their_own_account(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create(['is_active' => true]);

        Livewire::actingAs($owner)
            ->test('pages::admin.users.index')
            ->call('toggleActive', $owner->id)
            ->assertForbidden();

        $this->assertTrue($owner->fresh()->is_active);
    }

    public function test_owner_cannot_change_their_own_role(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();

        Livewire::actingAs($owner)
            ->test('pages::admin.users.form', ['user' => $owner])
            ->set('role', UserRole::AdminGudang->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(UserRole::Owner, $owner->fresh()->role);
    }

    public function test_owner_can_unlock_a_locked_account(): void
    {
        $user = User::factory()->role(UserRole::AdminGudang)->create([
            'failed_login_attempts' => 5,
            'locked_until' => now()->addMinutes(15),
        ]);
        $owner = User::factory()->role(UserRole::Owner)->create();

        Livewire::actingAs($owner)
            ->test('pages::admin.users.index')
            ->call('unlock', $user->id);

        $user->refresh();
        $this->assertSame(0, $user->failed_login_attempts);
        $this->assertNull($user->locked_until);
        $this->assertFalse($user->isLocked());
    }
}
