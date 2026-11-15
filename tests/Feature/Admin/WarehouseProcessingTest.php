<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Enums\WarehouseStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\User;
use App\Models\WarehouseProcessLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseProcessingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_marketplace_cannot_access_warehouse_module(): void
    {
        $user = User::factory()->role(UserRole::AdminMarketplace)->create();

        $this->actingAs($user)
            ->get(route('admin.warehouse.index'))
            ->assertForbidden();
    }

    public function test_owner_can_view_but_not_update_status(): void
    {
        $order = Order::factory()->create([
            'marketplace_status' => 'READY_TO_SHIP',
            'warehouse_status' => null,
        ]);
        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($owner)
            ->get(route('admin.warehouse.index'))
            ->assertOk();

        $this->actingAs($owner)
            ->patch(route('admin.warehouse.receive', $order))
            ->assertForbidden();

        $this->assertNull($order->fresh()->warehouse_status);
    }

    public function test_admin_gudang_can_mark_order_as_received(): void
    {
        $order = Order::factory()->create([
            'marketplace_status' => 'READY_TO_SHIP',
            'warehouse_status' => null,
        ]);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)
            ->patch(route('admin.warehouse.receive', $order))
            ->assertRedirect();

        $order->refresh();
        $this->assertSame(WarehouseStatus::Diterima, $order->warehouse_status);

        $this->assertDatabaseHas('warehouse_process_logs', [
            'order_id' => $order->id,
            'user_id' => $user->id,
            'from_status' => null,
            'to_status' => WarehouseStatus::Diterima->value,
        ]);
    }

    public function test_admin_gudang_can_advance_status_through_the_flow(): void
    {
        $order = Order::factory()->create([
            'marketplace_status' => 'READY_TO_SHIP',
            'warehouse_status' => WarehouseStatus::Diterima,
        ]);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)->patch(route('admin.warehouse.advance', $order))->assertRedirect();
        $this->assertSame(WarehouseStatus::Dikemas, $order->fresh()->warehouse_status);

        $this->actingAs($user)->patch(route('admin.warehouse.advance', $order))->assertRedirect();
        $this->assertSame(WarehouseStatus::SiapKirim, $order->fresh()->warehouse_status);

        $this->actingAs($user)->patch(route('admin.warehouse.advance', $order))->assertRedirect();
        $this->assertSame(WarehouseStatus::Dikirim, $order->fresh()->warehouse_status);

        $this->assertSame(3, WarehouseProcessLog::where('order_id', $order->id)->count());
    }

    public function test_cannot_advance_past_final_status(): void
    {
        $order = Order::factory()->create(['warehouse_status' => WarehouseStatus::Dikirim]);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)
            ->patch(route('admin.warehouse.advance', $order))
            ->assertForbidden();
    }

    public function test_cannot_mark_received_twice(): void
    {
        $order = Order::factory()->create(['warehouse_status' => WarehouseStatus::Diterima]);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)
            ->patch(route('admin.warehouse.receive', $order))
            ->assertForbidden();
    }

    public function test_can_filter_by_status(): void
    {
        Order::factory()->create(['shopee_order_sn' => 'SN-DITERIMA', 'warehouse_status' => WarehouseStatus::Diterima]);
        Order::factory()->create(['shopee_order_sn' => 'SN-DIKIRIM', 'warehouse_status' => WarehouseStatus::Dikirim]);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)
            ->get(route('admin.warehouse.index', ['status' => WarehouseStatus::Diterima->value]))
            ->assertOk()
            ->assertSee('SN-DITERIMA')
            ->assertDontSee('SN-DIKIRIM');
    }

    public function test_can_search_by_order_sn(): void
    {
        Order::factory()->create(['shopee_order_sn' => 'FINDME12345']);
        Order::factory()->create(['shopee_order_sn' => 'OTHERORDER99']);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)
            ->get(route('admin.warehouse.index', ['search' => 'FINDME']))
            ->assertOk()
            ->assertSee('FINDME12345')
            ->assertDontSee('OTHERORDER99');
    }

    public function test_can_search_by_tracking_number(): void
    {
        $order = Order::factory()->create(['shopee_order_sn' => 'ORDER-A']);
        Shipment::factory()->create(['order_id' => $order->id, 'tracking_number' => 'RESIUNIK123']);

        $otherOrder = Order::factory()->create(['shopee_order_sn' => 'ORDER-B']);
        Shipment::factory()->create(['order_id' => $otherOrder->id, 'tracking_number' => 'LAINNYA999']);

        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)
            ->get(route('admin.warehouse.index', ['search' => 'RESIUNIK']))
            ->assertOk()
            ->assertSee('ORDER-A')
            ->assertDontSee('ORDER-B');
    }

    public function test_can_filter_by_month(): void
    {
        Order::factory()->create([
            'shopee_order_sn' => 'ORDER-SEPTEMBER',
            'order_placed_at' => '2026-09-15 10:00:00',
        ]);
        Order::factory()->create([
            'shopee_order_sn' => 'ORDER-OCTOBER',
            'order_placed_at' => '2026-10-05 14:00:00',
        ]);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)
            ->get(route('admin.warehouse.index', ['month' => '2026-09']))
            ->assertOk()
            ->assertSee('ORDER-SEPTEMBER')
            ->assertDontSee('ORDER-OCTOBER');
    }

    public function test_can_filter_by_date_range(): void
    {
        Order::factory()->create([
            'shopee_order_sn' => 'ORDER-IN-RANGE',
            'order_placed_at' => '2026-09-10 10:00:00',
        ]);
        Order::factory()->create([
            'shopee_order_sn' => 'ORDER-BEFORE-RANGE',
            'order_placed_at' => '2026-09-01 10:00:00',
        ]);
        Order::factory()->create([
            'shopee_order_sn' => 'ORDER-AFTER-RANGE',
            'order_placed_at' => '2026-09-25 10:00:00',
        ]);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)
            ->get(route('admin.warehouse.index', ['date_from' => '2026-09-08', 'date_to' => '2026-09-15']))
            ->assertOk()
            ->assertSee('ORDER-IN-RANGE')
            ->assertDontSee('ORDER-BEFORE-RANGE')
            ->assertDontSee('ORDER-AFTER-RANGE');
    }

    public function test_can_filter_by_today_and_reset_filters(): void
    {
        Order::factory()->create([
            'shopee_order_sn' => 'ORDER-TODAY',
            'order_placed_at' => now(),
        ]);
        Order::factory()->create([
            'shopee_order_sn' => 'ORDER-LAST-MONTH',
            'order_placed_at' => now()->subMonths(2),
        ]);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)
            ->get(route('admin.warehouse.index', [
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('ORDER-TODAY')
            ->assertDontSee('ORDER-LAST-MONTH');

        $this->get(route('admin.warehouse.index'))
            ->assertOk()
            ->assertSee('ORDER-TODAY')
            ->assertSee('ORDER-LAST-MONTH');
    }

    public function test_admin_gudang_can_bulk_update_orders_status(): void
    {
        $order1 = Order::factory()->create([
            'marketplace_status' => 'READY_TO_SHIP',
            'warehouse_status' => WarehouseStatus::Diterima,
        ]);
        $order2 = Order::factory()->create([
            'marketplace_status' => 'READY_TO_SHIP',
            'warehouse_status' => WarehouseStatus::Diterima,
        ]);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)
            ->patch(route('admin.warehouse.bulk-update'), [
                'selected_order_ids' => [$order1->id, $order2->id],
                'target_status' => WarehouseStatus::Dikemas->value,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $order1->refresh();
        $order2->refresh();

        $this->assertSame(WarehouseStatus::Dikemas, $order1->warehouse_status);
        $this->assertSame(WarehouseStatus::Dikemas, $order2->warehouse_status);
        $this->assertSame(2, WarehouseProcessLog::where('to_status', 'dikemas')->where('user_id', $user->id)->count());
    }

    public function test_cancelled_orders_cannot_be_advanced_or_bulk_updated(): void
    {
        $cancelledOrder = Order::factory()->create([
            'marketplace_status' => 'CANCELLED',
            'warehouse_status' => WarehouseStatus::Diterima,
        ]);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)
            ->patch(route('admin.warehouse.advance', $cancelledOrder))
            ->assertForbidden();

        $cancelledOrder->refresh();
        $this->assertSame(WarehouseStatus::Diterima, $cancelledOrder->warehouse_status);

        $this->patch(route('admin.warehouse.bulk-update'), [
            'selected_order_ids' => [$cancelledOrder->id],
            'target_status' => WarehouseStatus::Dikemas->value,
        ])->assertRedirect();

        $cancelledOrder->refresh();
        $this->assertSame(WarehouseStatus::Diterima, $cancelledOrder->warehouse_status);
    }

    public function test_order_and_items_are_visible_on_warehouse_page(): void
    {
        $order = Order::factory()->create([
            'shopee_order_sn' => 'SN-SLIP-TEST',
            'buyer_username' => 'ibu_rahmawati',
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'item_name' => 'Beras Koshikari 5kg',
            'sku' => 'BS-KOSHI-5KG',
            'quantity' => 1,
        ]);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)
            ->get(route('admin.warehouse.index'))
            ->assertOk()
            ->assertSee('SN-SLIP-TEST')
            ->assertSee('Beras Koshikari 5kg')
            ->assertSee('BS-KOSHI-5KG');
    }
}
