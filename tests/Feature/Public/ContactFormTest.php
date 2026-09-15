<?php

namespace Tests\Feature\Public;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_submission_creates_contact_message(): void
    {
        Livewire::test('pages::public.kontak')
            ->set('name', 'Budi Santoso')
            ->set('email', 'budi@example.com')
            ->set('phone', '081234567890')
            ->set('message', 'Saya tertarik menjadi mitra distribusi.')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
        ]);
    }

    public function test_submission_requires_name_email_and_message(): void
    {
        Livewire::test('pages::public.kontak')
            ->set('name', '')
            ->set('email', '')
            ->set('message', '')
            ->call('submit')
            ->assertHasErrors(['name', 'email', 'message']);

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_submission_requires_valid_email(): void
    {
        Livewire::test('pages::public.kontak')
            ->set('name', 'Budi')
            ->set('email', 'not-an-email')
            ->set('message', 'Halo')
            ->call('submit')
            ->assertHasErrors(['email']);
    }

    public function test_honeypot_field_silently_blocks_submission(): void
    {
        Livewire::test('pages::public.kontak')
            ->set('name', 'Bot')
            ->set('email', 'bot@example.com')
            ->set('message', 'Spam message')
            ->set('website', 'https://spam.example.com')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertSame(0, ContactMessage::count());
    }
}
