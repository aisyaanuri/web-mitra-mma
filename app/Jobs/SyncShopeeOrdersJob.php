<?php

namespace App\Jobs;

use App\Enums\SyncStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SyncLog;
use App\Services\Shopee\ShopeeGatewayInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Validator;
use Throwable;

class SyncShopeeOrdersJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(ShopeeGatewayInterface $gateway): void
    {
        try {
            $summaries = $gateway->getOrderList(now()->subDay(), now());

            $synced = 0;
            foreach ($summaries as $summary) {
                $this->syncOrder($gateway, $summary['order_sn']);
                $synced++;
            }

            SyncLog::create([
                'job_type' => 'shopee_orders',
                'status' => SyncStatus::Success,
                'message' => __(':count pesanan berhasil disinkronkan.', ['count' => $synced]),
                'payload' => ['synced' => $synced],
            ]);
        } catch (Throwable $e) {
            SyncLog::create([
                'job_type' => 'shopee_orders',
                'status' => SyncStatus::Failed,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function syncOrder(ShopeeGatewayInterface $gateway, string $orderSn): void
    {
        $detail = $gateway->getOrderDetail($orderSn);

        $validated = Validator::make($detail, [
            'order_sn' => ['required', 'string'],
            'order_status' => ['required', 'string'],
            'buyer_username' => ['nullable', 'string'],
            'total_amount' => ['required', 'numeric'],
            'currency' => ['required', 'string', 'size:3'],
            'create_time' => ['required', 'integer'],
            'item_list' => ['array'],
        ])->validate();

        $order = Order::updateOrCreate(
            ['shopee_order_sn' => $validated['order_sn']],
            [
                'shop_id' => config('shopee.partner_id') ?? 'fake-shop',
                'marketplace_status' => $validated['order_status'],
                'buyer_username' => $validated['buyer_username'] ?? null,
                'total_amount' => $validated['total_amount'],
                'currency' => $validated['currency'],
                'order_placed_at' => Carbon::createFromTimestamp($validated['create_time']),
                'raw_payload' => $detail,
                'last_synced_at' => now(),
            ]
        );

        foreach ($detail['item_list'] as $item) {
            $this->syncItem($order, $item);
        }
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function syncItem(Order $order, array $item): void
    {
        $validated = Validator::make($item, [
            'item_id' => ['required'],
            'item_name' => ['required', 'string'],
            'model_sku' => ['nullable', 'string'],
            'model_quantity_purchased' => ['required', 'integer', 'min:1'],
            'model_discounted_price' => ['required', 'numeric'],
        ])->validate();

        $product = $validated['model_sku']
            ? Product::where('sku', $validated['model_sku'])->first()
            : null;

        OrderItem::updateOrCreate(
            [
                'order_id' => $order->id,
                'shopee_item_id' => (string) $validated['item_id'],
            ],
            [
                'product_id' => $product?->id,
                'item_name' => $validated['item_name'],
                'sku' => $validated['model_sku'] ?? null,
                'quantity' => $validated['model_quantity_purchased'],
                'unit_price' => $validated['model_discounted_price'],
                'subtotal' => $validated['model_quantity_purchased'] * $validated['model_discounted_price'],
            ]
        );
    }
}
