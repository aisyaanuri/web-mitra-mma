<?php

namespace Tests\Feature\Public;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_submission_creates_contact_message(): void
    {
        $this->post(route('kontak.store'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'message' => 'Saya tertarik menjadi mitra distribusi.',
        ])->assertRedirect(route('kontak'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
        ]);
    }

    public function test_submission_requires_name_email_and_message(): void
    {
        $this->from(route('kontak'))
            ->post(route('kontak.store'), ['name' => '', 'email' => '', 'message' => ''])
            ->assertRedirect(route('kontak'))
            ->assertSessionHasErrors(['name', 'email', 'message']);

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_submission_requires_valid_email(): void
    {
        $this->from(route('kontak'))
            ->post(route('kontak.store'), [
                'name' => 'Budi',
                'email' => 'not-an-email',
                'message' => 'Halo',
            ])
            ->assertRedirect(route('kontak'))
            ->assertSessionHasErrors(['email']);
    }

    public function test_honeypot_field_silently_blocks_submission(): void
    {
        $this->post(route('kontak.store'), [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'message' => 'Spam message',
            'website' => 'https://spam.example.com',
        ])->assertRedirect(route('kontak'))
            ->assertSessionHasNoErrors();

        $this->assertSame(0, ContactMessage::count());
    }
}
