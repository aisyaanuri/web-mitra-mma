<?php

namespace Tests\Feature\Admin;

use App\Enums\ReconciliationStatus;
use App\Enums\UserRole;
use App\Enums\WarehouseStatus;
use App\Models\Order;
use App\Models\ReconciliationRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_marketplace_cannot_export_warehouse_orders(): void
    {
        $user = User::factory()->role(UserRole::AdminMarketplace)->create();

        $this->actingAs($user)
            ->get(route('admin.warehouse.export'))
            ->assertForbidden();
    }

    public function test_admin_gudang_can_export_warehouse_orders(): void
    {
        Order::factory()->create(['warehouse_status' => WarehouseStatus::Diterima]);
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $response = $this->actingAs($user)->get(route('admin.warehouse.export'));

        $response->assertOk();
        $response->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );
    }

    public function test_admin_gudang_cannot_export_reconciliation_report(): void
    {
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)
            ->get(route('admin.reconciliation.export'))
            ->assertForbidden();
    }

    public function test_owner_can_export_reconciliation_report(): void
    {
        $order = Order::factory()->create();
        ReconciliationRecord::factory()->create([
            'order_id' => $order->id,
            'match_status' => ReconciliationStatus::Selisih,
        ]);

        $user = User::factory()->role(UserRole::Owner)->create();

        $response = $this->actingAs($user)->get(route('admin.reconciliation.export'));

        $response->assertOk();
        $response->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );
    }
}
