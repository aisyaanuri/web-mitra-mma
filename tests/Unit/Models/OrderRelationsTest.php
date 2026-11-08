<?php

namespace Tests\Unit\Models;

use App\Enums\ReconciliationStatus;
use App\Enums\WarehouseStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\ReconciliationRecord;
use App\Models\Shipment;
use App\Models\User;
use App\Models\WarehouseProcessLog;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_casts_warehouse_status_to_enum(): void
    {
        $order = Order::factory()->create(['warehouse_status' => 'dikemas']);

        $this->assertSame(WarehouseStatus::Dikemas, $order->warehouse_status);
    }

    public function test_order_relationships_resolve(): void
    {
        $product = Product::factory()->create();
        $order = Order::factory()->create();

        OrderItem::factory()->forProduct($product)->create(['order_id' => $order->id]);
        Shipment::factory()->create(['order_id' => $order->id]);
        OrderReturn::factory()->create(['order_id' => $order->id]);
        WarehouseProcessLog::create([
            'order_id' => $order->id,
            'user_id' => User::factory()->create()->id,
            'from_status' => null,
            'to_status' => 'diterima',
        ]);
        ReconciliationRecord::factory()->create([
            'order_id' => $order->id,
            'match_status' => ReconciliationStatus::Sesuai,
        ]);

        $order->refresh();

        $this->assertCount(1, $order->items);
        $this->assertSame($product->id, $order->items->first()->product->id);
        $this->assertCount(1, $order->shipments);
        $this->assertCount(1, $order->returns);
        $this->assertCount(1, $order->processLogs);
        $this->assertInstanceOf(ReconciliationRecord::class, $order->reconciliationRecord);
        $this->assertSame(ReconciliationStatus::Sesuai, $order->reconciliationRecord->match_status);
    }

    public function test_reconciliation_record_is_unique_per_order(): void
    {
        $order = Order::factory()->create();
        ReconciliationRecord::factory()->create(['order_id' => $order->id]);

        $this->expectException(QueryException::class);

        ReconciliationRecord::factory()->create(['order_id' => $order->id]);
    }
}
