<?php

namespace Tests\Unit\Jobs;

use App\Enums\ReconciliationStatus;
use App\Enums\WarehouseStatus;
use App\Jobs\RunReconciliationJob;
use App\Models\Order;
use App\Models\ReconciliationRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RunReconciliationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_marketplace_order_shipped_by_warehouse_is_sesuai(): void
    {
        $order = Order::factory()->create([
            'marketplace_status' => 'COMPLETED',
            'warehouse_status' => WarehouseStatus::Dikirim,
        ]);

        (new RunReconciliationJob)->handle();

        $this->assertSame(
            ReconciliationStatus::Sesuai,
            ReconciliationRecord::where('order_id', $order->id)->first()->match_status,
        );
    }

    public function test_completed_marketplace_order_not_yet_shipped_is_selisih(): void
    {
        $order = Order::factory()->create([
            'marketplace_status' => 'COMPLETED',
            'warehouse_status' => WarehouseStatus::Dikemas,
        ]);

        (new RunReconciliationJob)->handle();

        $this->assertSame(
            ReconciliationStatus::Selisih,
            ReconciliationRecord::where('order_id', $order->id)->first()->match_status,
        );
    }

    public function test_cancelled_order_shipped_anyway_is_selisih(): void
    {
        $order = Order::factory()->create([
            'marketplace_status' => 'CANCELLED',
            'warehouse_status' => WarehouseStatus::Dikirim,
        ]);

        (new RunReconciliationJob)->handle();

        $this->assertSame(
            ReconciliationStatus::Selisih,
            ReconciliationRecord::where('order_id', $order->id)->first()->match_status,
        );
    }

    public function test_cancelled_order_correctly_not_shipped_is_sesuai(): void
    {
        $order = Order::factory()->create([
            'marketplace_status' => 'CANCELLED',
            'warehouse_status' => WarehouseStatus::Diterima,
        ]);

        (new RunReconciliationJob)->handle();

        $this->assertSame(
            ReconciliationStatus::Sesuai,
            ReconciliationRecord::where('order_id', $order->id)->first()->match_status,
        );
    }

    public function test_in_progress_order_is_belum_dicek(): void
    {
        $order = Order::factory()->create([
            'marketplace_status' => 'READY_TO_SHIP',
            'warehouse_status' => WarehouseStatus::Diterima,
        ]);

        (new RunReconciliationJob)->handle();

        $this->assertSame(
            ReconciliationStatus::BelumDicek,
            ReconciliationRecord::where('order_id', $order->id)->first()->match_status,
        );
    }

    public function test_rerunning_the_job_preserves_existing_investigation_note(): void
    {
        $order = Order::factory()->create([
            'marketplace_status' => 'COMPLETED',
            'warehouse_status' => WarehouseStatus::Dikemas,
        ]);

        (new RunReconciliationJob)->handle();

        $record = ReconciliationRecord::where('order_id', $order->id)->first();
        $record->update(['investigation_note' => 'Sudah dicek manual, barang tertahan di gudang.']);

        (new RunReconciliationJob)->handle();

        $this->assertSame(
            'Sudah dicek manual, barang tertahan di gudang.',
            $record->fresh()->investigation_note,
        );
    }
}
