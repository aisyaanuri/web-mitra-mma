<?php

namespace Tests\Feature\Admin;

use App\Enums\ReconciliationStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\ReconciliationRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

        Livewire::actingAs($owner)
            ->test('pages::admin.reconciliation.index')
            ->call('runReconciliation');

        $this->assertSame(1, ReconciliationRecord::count());
    }

    public function test_can_filter_by_match_status(): void
    {
        $order = Order::factory()->create(['shopee_order_sn' => 'SESUAI-ORDER']);
        ReconciliationRecord::factory()->create(['order_id' => $order->id, 'match_status' => ReconciliationStatus::Sesuai]);

        $otherOrder = Order::factory()->create(['shopee_order_sn' => 'SELISIH-ORDER']);
        ReconciliationRecord::factory()->create(['order_id' => $otherOrder->id, 'match_status' => ReconciliationStatus::Selisih]);

        $owner = User::factory()->role(UserRole::Owner)->create();

        Livewire::actingAs($owner)
            ->test('pages::admin.reconciliation.index')
            ->set('matchStatus', ReconciliationStatus::Selisih->value)
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

        Livewire::actingAs($owner)
            ->test('pages::admin.reconciliation.index')
            ->call('editNote', $record->id)
            ->set('noteText', 'Barang masih dalam proses packing, belum sempat kirim.')
            ->call('saveNote')
            ->assertHasNoErrors();

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

        Livewire::actingAs($owner)
            ->test('pages::admin.reconciliation.index')
            ->set('period', '2026-01')
            ->assertSee('JAN-ORDER')
            ->assertDontSee('FEB-ORDER');
    }
}
