<?php

namespace Tests\Feature\Public;

use App\Models\CompanyContent;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_beranda_renders_with_seeded_content(): void
    {
        CompanyContent::factory()->create(['section_key' => 'beranda_hero', 'title' => 'Hero Title']);
        SiteSetting::factory()->create(['key' => 'outlets_count', 'value' => '355']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Hero Title')
            ->assertSee('355');
    }

    public function test_tentang_kami_renders(): void
    {
        CompanyContent::factory()->create(['section_key' => 'tentang_kami', 'title' => 'Cerita Kami']);

        $this->get(route('tentang-kami'))
            ->assertOk()
            ->assertSee('Cerita Kami');
    }

    public function test_tentang_kami_hides_visi_misi_when_not_published(): void
    {
        $this->get(route('tentang-kami'))
            ->assertOk()
            ->assertDontSee('Visi');
    }

    public function test_kontak_renders_with_office_address(): void
    {
        SiteSetting::factory()->create(['key' => 'office_address', 'value' => 'Jl. Contoh No. 1']);

        $this->get(route('kontak'))
            ->assertOk()
            ->assertSee('Jl. Contoh No. 1');
    }

    public function test_sitemap_lists_public_pages(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertSee(route('home'), false);
        $response->assertSee(route('tentang-kami'), false);
        $response->assertSee(route('kontak'), false);
    }
}
