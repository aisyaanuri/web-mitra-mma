<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_dashboard_displays_sla_overdue_warning_for_stuck_orders(): void
    {
        $owner = User::factory()->role(\App\Enums\UserRole::Owner)->create();

        \App\Models\Order::factory()->create([
            'order_placed_at' => now()->subHours(36),
            'warehouse_status' => null,
            'marketplace_status' => 'READY_TO_SHIP',
        ]);

        \Livewire\Livewire::actingAs($owner)
            ->test('pages::dashboard')
            ->assertSee('Peringatan SLA Gudang')
            ->assertSee('Mengendap > 24 Jam');
    }

    public function test_dashboard_displays_top_selling_products(): void
    {
        $owner = User::factory()->role(\App\Enums\UserRole::Owner)->create();
        $order = \App\Models\Order::factory()->create(['marketplace_status' => 'COMPLETED']);

        \App\Models\OrderItem::factory()->create([
            'order_id' => $order->id,
            'item_name' => 'Beras Organik Mentik Wangi 5kg',
            'sku' => 'BOMW-5KG',
            'quantity' => 25,
            'subtotal' => 3750000,
        ]);

        \Livewire\Livewire::actingAs($owner)
            ->test('pages::dashboard')
            ->assertSee('Produk Terlaris')
            ->assertSee('Beras Organik Mentik Wangi 5kg')
            ->assertSee('BOMW-5KG')
            ->assertSee('25 pcs');
    }
}
