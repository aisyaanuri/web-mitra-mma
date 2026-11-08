<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Enums\WarehouseStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SyncLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncStatusPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_gudang_cannot_access_sync_status(): void
    {
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)
            ->get(route('admin.sync.index'))
            ->assertForbidden();
    }

    public function test_owner_and_admin_marketplace_can_view_sync_status(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();
        $marketplace = User::factory()->role(UserRole::AdminMarketplace)->create();

        $this->actingAs($owner)->get(route('admin.sync.index'))->assertOk();
        $this->actingAs($marketplace)->get(route('admin.sync.index'))->assertOk();
    }

    public function test_sync_now_button_runs_the_sync_jobs_and_logs_results(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($owner)
            ->post(route('admin.sync.run'))
            ->assertRedirect();

        $this->assertTrue(SyncLog::where('job_type', 'shopee_orders')->exists());
        $this->assertTrue(SyncLog::where('job_type', 'shopee_logistics')->exists());
        $this->assertTrue(SyncLog::where('job_type', 'shopee_returns')->exists());
    }

    public function test_shows_fake_mode_warning_by_default(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($owner)
            ->get(route('admin.sync.index'))
            ->assertSee('Fake (Data Simulasi)');
    }

    public function test_sync_log_is_displayed_on_the_page(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();
        $log = SyncLog::create([
            'job_type' => 'shopee_orders',
            'status' => 'success',
            'message' => '2 pesanan berhasil disinkronkan.',
            'payload' => [
                'synced_count' => 2,
                'orders' => [
                    [
                        'order_sn' => 'SP-TEST-999',
                        'buyer' => 'Ibu Siti Khadijah',
                        'amount' => 175000,
                        'status' => 'READY_TO_SHIP',
                        'items_count' => 3,
                    ],
                ],
            ],
            'created_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('admin.sync.index'))
            ->assertOk()
            ->assertSee('2 pesanan berhasil disinkronkan.');
    }

    public function test_can_search_and_view_synced_orders(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();
        $order = Order::factory()->create([
            'shopee_order_sn' => 'SP-SYNC-VIEW-01',
            'buyer_username' => 'budisantoso99',
            'total_amount' => 250000,
            'marketplace_status' => 'PROCESSED',
            'warehouse_status' => WarehouseStatus::Dikemas,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'item_name' => 'Beras Pandan Wangi Super 5kg',
            'sku' => 'BPW-5KG',
            'quantity' => 2,
            'unit_price' => 125000,
            'subtotal' => 250000,
        ]);

        $this->actingAs($owner)
            ->get(route('admin.sync.index', ['order_search' => 'budisantoso99']))
            ->assertOk()
            ->assertSee('SP-SYNC-VIEW-01')
            ->assertSee('budisantoso99')
            ->assertSee('Beras Pandan Wangi Super 5kg')
            ->assertSee('BPW-5KG');
    }
}
