<?php

namespace App\Jobs;

use App\Enums\SyncStatus;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\SyncLog;
use App\Services\Shopee\ShopeeGatewayInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Validator;
use Throwable;

class SyncShopeeReturnsJob implements ShouldQueue
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
            $returns = $gateway->getReturnList(now()->subDays(7), now());

            $synced = 0;
            foreach ($returns as $returnData) {
                if ($this->syncReturn($returnData)) {
                    $synced++;
                }
            }

            SyncLog::create([
                'job_type' => 'shopee_returns',
                'status' => SyncStatus::Success,
                'message' => __(':count retur disinkronkan.', ['count' => $synced]),
                'payload' => ['synced' => $synced],
            ]);
        } catch (Throwable $e) {
            SyncLog::create([
                'job_type' => 'shopee_returns',
                'status' => SyncStatus::Failed,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $returnData
     */
    private function syncReturn(array $returnData): bool
    {
        $validated = Validator::make($returnData, [
            'return_sn' => ['required', 'string'],
            'order_sn' => ['required', 'string'],
            'reason' => ['nullable', 'string'],
            'status' => ['required', 'string'],
            'refund_amount' => ['required', 'numeric'],
        ])->validate();

        $order = Order::where('shopee_order_sn', $validated['order_sn'])->first();

        if (! $order) {
            // Order hasn't been synced yet; a later orders sync + return
            // sync pass will pick this up.
            return false;
        }

        OrderReturn::updateOrCreate(
            ['shopee_return_sn' => $validated['return_sn']],
            [
                'order_id' => $order->id,
                'reason' => $validated['reason'] ?? null,
                'status' => $validated['status'],
                'refund_amount' => $validated['refund_amount'],
                'raw_payload' => $returnData,
            ]
        );

        return true;
    }
}
