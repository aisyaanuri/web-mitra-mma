<?php

namespace Tests\Feature\Admin;

use App\Enums\ReconciliationStatus;
use App\Enums\UserRole;
use App\Enums\WarehouseStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReconciliationRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReconciliationReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_gudang_cannot_access_reconciliation_report(): void
    {
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)
            ->get(route('admin.reconciliation.index'))
            ->assertForbidden();
    }

    public function test_owner_and_admin_marketplace_can_view_report(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();
        $marketplace = User::factory()->role(UserRole::AdminMarketplace)->create();

        $this->actingAs($owner)->get(route('admin.reconciliation.index'))->assertOk();
        $this->actingAs($marketplace)->get(route('admin.reconciliation.index'))->assertOk();
    }

    public function test_run_reconciliation_button_generates_records(): void
    {
        Order::factory()->create(['marketplace_status' => 'COMPLETED', 'warehouse_status' => 'dikirim']);
        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($owner)
            ->post(route('admin.reconciliation.run'))
            ->assertRedirect();

        $this->assertSame(1, ReconciliationRecord::count());
    }

    public function test_can_filter_by_match_status(): void
    {
        $order = Order::factory()->create(['shopee_order_sn' => 'SESUAI-ORDER']);
        ReconciliationRecord::factory()->create(['order_id' => $order->id, 'match_status' => ReconciliationStatus::Sesuai]);

        $otherOrder = Order::factory()->create(['shopee_order_sn' => 'SELISIH-ORDER']);
        ReconciliationRecord::factory()->create(['order_id' => $otherOrder->id, 'match_status' => ReconciliationStatus::Selisih]);

        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($owner)
            ->get(route('admin.reconciliation.index', ['match_status' => ReconciliationStatus::Selisih->value]))
            ->assertOk()
            ->assertSee('SELISIH-ORDER')
            ->assertDontSee('SESUAI-ORDER');
    }

    public function test_can_add_investigation_note_on_a_selisih_record(): void
    {
        $order = Order::factory()->create();
        $record = ReconciliationRecord::factory()->create([
            'order_id' => $order->id,
            'match_status' => ReconciliationStatus::Selisih,
        ]);

        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($owner)
            ->patch(route('admin.reconciliation.note', $record), [
                'investigation_note' => 'Barang masih dalam proses packing, belum sempat kirim.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $record->refresh();
        $this->assertSame('Barang masih dalam proses packing, belum sempat kirim.', $record->investigation_note);
        $this->assertSame($owner->id, $record->checked_by);
        $this->assertNotNull($record->checked_at);
    }

    public function test_can_filter_by_period(): void
    {
        $januaryOrder = Order::factory()->create(['shopee_order_sn' => 'JAN-ORDER']);
        ReconciliationRecord::factory()->create([
            'order_id' => $januaryOrder->id,
            'period' => '2026-01-01',
        ]);

        $februaryOrder = Order::factory()->create(['shopee_order_sn' => 'FEB-ORDER']);
        ReconciliationRecord::factory()->create([
            'order_id' => $februaryOrder->id,
            'period' => '2026-02-01',
        ]);

        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($owner)
            ->get(route('admin.reconciliation.index', ['period' => '2026-01']))
            ->assertOk()
            ->assertSee('JAN-ORDER')
            ->assertDontSee('FEB-ORDER');
    }

    public function test_can_search_by_order_sn_or_customer(): void
    {
        $orderA = Order::factory()->create([
            'shopee_order_sn' => 'SEARCH-ABC-111',
            'buyer_username' => 'ahmaddahlan',
        ]);
        ReconciliationRecord::factory()->create(['order_id' => $orderA->id]);

        $orderB = Order::factory()->create([
            'shopee_order_sn' => 'SEARCH-XYZ-222',
            'buyer_username' => 'kurniawaneka',
        ]);
        ReconciliationRecord::factory()->create(['order_id' => $orderB->id]);

        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($owner)
            ->get(route('admin.reconciliation.index', ['search' => 'ahmad']))
            ->assertOk()
            ->assertSee('SEARCH-ABC-111')
            ->assertDontSee('SEARCH-XYZ-222');
    }

    public function test_reconciliation_page_displays_order_comparison_and_items(): void
    {
        $order = Order::factory()->create([
            'shopee_order_sn' => 'INVESTIGATE-001',
            'marketplace_status' => 'COMPLETED',
            'warehouse_status' => WarehouseStatus::Diterima,
            'buyer_username' => 'hjmardiah',
            'total_amount' => 320000,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'item_name' => 'Yummy Bites Beberoll Blueberry 40g',
            'sku' => 'YB-BBL-40G',
            'quantity' => 4,
            'unit_price' => 80000,
            'subtotal' => 320000,
        ]);

        $record = ReconciliationRecord::factory()->create([
            'order_id' => $order->id,
            'match_status' => ReconciliationStatus::Selisih,
        ]);

        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($owner)
            ->get(route('admin.reconciliation.index'))
            ->assertOk()
            ->assertSee('INVESTIGATE-001')
            ->assertSee('hjmardiah')
            ->assertSee('Yummy Bites Beberoll Blueberry 40g')
            ->assertSee('YB-BBL-40G');
    }

    public function test_can_resolve_warehouse_status_from_investigation(): void
    {
        $order = Order::factory()->create([
            'shopee_order_sn' => 'RESOLVE-999',
            'marketplace_status' => 'COMPLETED',
            'warehouse_status' => WarehouseStatus::Diterima,
        ]);
        $record = ReconciliationRecord::factory()->create([
            'order_id' => $order->id,
            'match_status' => ReconciliationStatus::Selisih,
        ]);

        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($owner)
            ->patch(route('admin.reconciliation.warehouse-status', $record), [
                'warehouse_status' => WarehouseStatus::Dikirim->value,
            ])
            ->assertRedirect();

        $order->refresh();
        $record->refresh();

        $this->assertSame(WarehouseStatus::Dikirim, $order->warehouse_status);
        $this->assertSame(ReconciliationStatus::Sesuai, $record->match_status);
        $this->assertDatabaseHas('warehouse_process_logs', [
            'order_id' => $order->id,
            'to_status' => 'dikirim',
            'user_id' => $owner->id,
        ]);
    }

    public function test_export_excel_with_search_parameter(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();
        $order = Order::factory()->create(['shopee_order_sn' => 'EXP-999-TEST']);
        ReconciliationRecord::factory()->create(['order_id' => $order->id]);

        $response = $this->actingAs($owner)
            ->get(route('admin.reconciliation.export', ['search' => 'EXP-999']));

        $response->assertOk();
        $this->assertTrue(str_contains(
            $response->headers->get('content-disposition', ''),
            'attachment'
        ));
    }
}
