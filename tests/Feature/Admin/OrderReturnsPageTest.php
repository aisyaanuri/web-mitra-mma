<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderReturnsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_returns_page(): void
    {
        $this->get(route('admin.returns.index'))
            ->assertRedirect(route('login'));
    }

    public function test_all_admin_roles_can_view_returns_page(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();
        $marketplace = User::factory()->role(UserRole::AdminMarketplace)->create();
        $gudang = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($owner)->get(route('admin.returns.index'))->assertOk();
        $this->actingAs($marketplace)->get(route('admin.returns.index'))->assertOk();
        $this->actingAs($gudang)->get(route('admin.returns.index'))->assertOk();
    }

    public function test_can_search_and_filter_returns(): void
    {
        $orderA = Order::factory()->create(['shopee_order_sn' => 'ORD-RETURN-A']);
        $retA = OrderReturn::factory()->create([
            'order_id' => $orderA->id,
            'shopee_return_sn' => 'RET-SPECIAL-111',
            'reason' => 'Barang rusak kemasan basah',
        ]);

        $orderB = Order::factory()->create(['shopee_order_sn' => 'ORD-RETURN-B']);
        $retB = OrderReturn::factory()->create([
            'order_id' => $orderB->id,
            'shopee_return_sn' => 'RET-OTHER-999',
            'reason' => 'Salah ukuran',
        ]);

        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($owner)
            ->get(route('admin.returns.index', ['search' => 'SPECIAL']))
            ->assertOk()
            ->assertSee('RET-SPECIAL-111')
            ->assertDontSee('RET-OTHER-999');
    }

    public function test_can_verify_physical_return(): void
    {
        $order = Order::factory()->create();
        $return = OrderReturn::factory()->create([
            'order_id' => $order->id,
            'physical_status' => 'menunggu_barang',
        ]);

        $gudang = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($gudang)
            ->patch(route('admin.returns.verify', $return), [
                'physical_status' => 'diterima_rusak',
                'physical_note' => 'Segel terbuka dan beras kemasan 5kg bocor.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $return->refresh();

        $this->assertSame('diterima_rusak', $return->physical_status);
        $this->assertSame('Segel terbuka dan beras kemasan 5kg bocor.', $return->physical_note);
        $this->assertSame($gudang->id, $return->verified_by);
        $this->assertNotNull($return->received_at);
    }
}
