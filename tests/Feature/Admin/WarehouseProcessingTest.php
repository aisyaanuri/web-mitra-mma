<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Enums\WarehouseStatus;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use App\Models\WarehouseProcessLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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
        $order = Order::factory()->create(['warehouse_status' => null]);
        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($owner)
            ->get(route('admin.warehouse.index'))
            ->assertOk();

        Livewire::actingAs($owner)
            ->test('pages::admin.warehouse.index')
            ->call('markReceived', $order->id)
            ->assertForbidden();

        $this->assertNull($order->fresh()->warehouse_status);
    }

    public function test_admin_gudang_can_mark_order_as_received(): void
    {
        $order = Order::factory()->create(['warehouse_status' => null]);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        Livewire::actingAs($user)
            ->test('pages::admin.warehouse.index')
            ->call('markReceived', $order->id);

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
        $order = Order::factory()->create(['warehouse_status' => WarehouseStatus::Diterima]);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $component = Livewire::actingAs($user)->test('pages::admin.warehouse.index');

        $component->call('advance', $order->id);
        $this->assertSame(WarehouseStatus::Dikemas, $order->fresh()->warehouse_status);

        $component->call('advance', $order->id);
        $this->assertSame(WarehouseStatus::SiapKirim, $order->fresh()->warehouse_status);

        $component->call('advance', $order->id);
        $this->assertSame(WarehouseStatus::Dikirim, $order->fresh()->warehouse_status);

        $this->assertSame(3, WarehouseProcessLog::where('order_id', $order->id)->count());
    }

    public function test_cannot_advance_past_final_status(): void
    {
        $order = Order::factory()->create(['warehouse_status' => WarehouseStatus::Dikirim]);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        Livewire::actingAs($user)
            ->test('pages::admin.warehouse.index')
            ->call('advance', $order->id)
            ->assertForbidden();
    }

    public function test_cannot_mark_received_twice(): void
    {
        $order = Order::factory()->create(['warehouse_status' => WarehouseStatus::Diterima]);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        Livewire::actingAs($user)
            ->test('pages::admin.warehouse.index')
            ->call('markReceived', $order->id)
            ->assertForbidden();
    }

    public function test_can_filter_by_status(): void
    {
        Order::factory()->create(['shopee_order_sn' => 'SN-DITERIMA', 'warehouse_status' => WarehouseStatus::Diterima]);
        Order::factory()->create(['shopee_order_sn' => 'SN-DIKIRIM', 'warehouse_status' => WarehouseStatus::Dikirim]);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        Livewire::actingAs($user)
            ->test('pages::admin.warehouse.index')
            ->set('status', WarehouseStatus::Diterima->value)
            ->assertSee('SN-DITERIMA')
            ->assertDontSee('SN-DIKIRIM');
    }

    public function test_can_search_by_order_sn(): void
    {
        Order::factory()->create(['shopee_order_sn' => 'FINDME12345']);
        Order::factory()->create(['shopee_order_sn' => 'OTHERORDER99']);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        Livewire::actingAs($user)
            ->test('pages::admin.warehouse.index')
            ->set('search', 'FINDME')
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

        Livewire::actingAs($user)
            ->test('pages::admin.warehouse.index')
            ->set('search', 'RESIUNIK')
            ->assertSee('ORDER-A')
            ->assertDontSee('ORDER-B');
    }
}
