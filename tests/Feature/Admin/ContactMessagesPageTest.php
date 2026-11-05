<?php

namespace Tests\Feature\Admin;

use App\Enums\ContactMessageStatus;
use App\Enums\UserRole;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContactMessagesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.messages.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_gudang_cannot_access_messages_page(): void
    {
        $gudang = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($gudang)
            ->get(route('admin.messages.index'))
            ->assertForbidden();
    }

    public function test_owner_can_access_messages_page(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($owner)
            ->get(route('admin.messages.index'))
            ->assertOk();
    }

    public function test_admin_marketplace_can_access_messages_page(): void
    {
        $mp = User::factory()->role(UserRole::AdminMarketplace)->create();

        $this->actingAs($mp)
            ->get(route('admin.messages.index'))
            ->assertOk();
    }

    public function test_messages_are_displayed_and_can_be_filtered(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();

        ContactMessage::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'message' => 'Tawaran kemitraan supermarket.',
            'status' => ContactMessageStatus::New,
        ]);

        ContactMessage::factory()->create([
            'name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'message' => 'Pemesanan beras 50 karung.',
            'status' => ContactMessageStatus::Read,
        ]);

        Livewire::actingAs($owner)
            ->test('pages::admin.messages.index')
            ->assertSee('Budi Santoso')
            ->assertSee('Siti Aminah')
            ->set('search', 'Budi')
            ->assertSee('Budi Santoso')
            ->assertDontSee('Siti Aminah')
            ->set('search', '')
            ->set('status', 'read')
            ->assertSee('Siti Aminah')
            ->assertDontSee('Budi Santoso');
    }

    public function test_opening_message_auto_marks_it_as_read(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();

        $msg = ContactMessage::factory()->create([
            'status' => ContactMessageStatus::New,
        ]);

        Livewire::actingAs($owner)
            ->test('pages::admin.messages.index')
            ->call('openMessage', $msg->id);

        $msg->refresh();
        $this->assertSame(ContactMessageStatus::Read, $msg->status);
    }

    public function test_admin_can_update_status_and_delete_message(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();

        $msg = ContactMessage::factory()->create([
            'status' => ContactMessageStatus::Read,
        ]);

        Livewire::actingAs($owner)
            ->test('pages::admin.messages.index')
            ->call('updateStatus', $msg->id, 'replied');

        $msg->refresh();
        $this->assertSame(ContactMessageStatus::Replied, $msg->status);

        Livewire::actingAs($owner)
            ->test('pages::admin.messages.index')
            ->call('deleteMessage', $msg->id);

        $this->assertDatabaseMissing('contact_messages', ['id' => $msg->id]);
    }
}

