<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyPolicyPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_policy_page_renders(): void
    {
        $this->get(route('kebijakan-privasi'))
            ->assertOk()
            ->assertSee('Kebijakan Privasi')
            ->assertSee('27 Tahun 2022');
    }

    public function test_contact_page_links_to_privacy_policy(): void
    {
        $this->get(route('kontak'))
            ->assertSee(route('kebijakan-privasi'), false);
    }

    public function test_sitemap_includes_privacy_policy(): void
    {
        $this->get('/sitemap.xml')
            ->assertSee(route('kebijakan-privasi'), false);
    }
}
