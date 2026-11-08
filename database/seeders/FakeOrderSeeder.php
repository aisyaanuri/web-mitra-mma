<?php

namespace Database\Seeders;

use App\Enums\ReconciliationStatus;
use App\Enums\UserRole;
use App\Enums\WarehouseStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\ReconciliationRecord;
use App\Models\Shipment;
use App\Models\User;
use App\Models\WarehouseProcessLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Seeds Shopee-shaped fake orders (items, shipments, occasional returns,
 * process history, reconciliation records) so the warehouse and
 * reconciliation modules can be built and demoed before real Shopee API
 * credentials exist (see FakeShopeeGateway in the implementation plan).
 */
class FakeOrderSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $gudangUsers = User::where('role', UserRole::AdminGudang)->get();

        Order::factory()
            ->count(30)
            ->create()
            ->each(function (Order $order) use ($products, $gudangUsers) {
                $this->seedItems($order, $products);

                Shipment::factory()->create(['order_id' => $order->id]);

                if (fake()->boolean(15)) {
                    OrderReturn::factory()->create(['order_id' => $order->id]);
                }

                $this->seedProcessHistory($order, $gudangUsers);

                if (fake()->boolean(80)) {
                    $this->seedReconciliation($order);
                }
            });
    }

    /** @param  Collection<int, Product>  $products */
    private function seedItems(Order $order, Collection $products): void
    {
        $itemCount = fake()->numberBetween(1, 3);
        $selected = $products->isNotEmpty()
            ? $products->random(min($itemCount, $products->count()))
            : collect();

        foreach ($selected as $product) {
            OrderItem::factory()->forProduct($product)->create(['order_id' => $order->id]);
        }

        if ($selected->isEmpty()) {
            OrderItem::factory()->create(['order_id' => $order->id]);
        }
    }

    /** @param  Collection<int, User>  $gudangUsers */
    private function seedProcessHistory(Order $order, Collection $gudangUsers): void
    {
        $flow = [
            WarehouseStatus::Diterima,
            WarehouseStatus::Dikemas,
            WarehouseStatus::SiapKirim,
            WarehouseStatus::Dikirim,
        ];

        $targetIndex = array_search($order->warehouse_status, $flow, true);

        if ($targetIndex === false) {
            return;
        }

        $previous = null;
        $timestamp = $order->order_placed_at->copy();
        $user = $gudangUsers->isNotEmpty() ? $gudangUsers->random() : User::factory()->create();

        for ($i = 0; $i <= $targetIndex; $i++) {
            WarehouseProcessLog::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'from_status' => $previous?->value,
                'to_status' => $flow[$i]->value,
                'ip_address' => fake()->ipv4(),
                'created_at' => $timestamp,
            ]);

            $previous = $flow[$i];
            $timestamp = $timestamp->copy()->addHours(fake()->numberBetween(2, 24));
        }
    }

    private function seedReconciliation(Order $order): void
    {
        $matchStatus = fake()->randomElement(ReconciliationStatus::cases());

        ReconciliationRecord::factory()->create([
            'order_id' => $order->id,
            'marketplace_status_snapshot' => $order->marketplace_status,
            'warehouse_status_snapshot' => $order->warehouse_status?->value,
            'match_status' => $matchStatus,
            'checked_at' => $matchStatus === ReconciliationStatus::BelumDicek ? null : now(),
            'investigation_note' => $matchStatus === ReconciliationStatus::Selisih
                ? fake()->sentence()
                : null,
            'period' => $order->order_placed_at->copy()->startOfMonth(),
        ]);
    }
}
