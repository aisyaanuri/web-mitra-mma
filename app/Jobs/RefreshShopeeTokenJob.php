<?php

namespace App\Jobs;

use App\Enums\SyncStatus;
use App\Models\ShopeeToken;
use App\Models\SyncLog;
use App\Services\Shopee\ShopeeAuthService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class RefreshShopeeTokenJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function handle(ShopeeAuthService $authService): void
    {
        ShopeeToken::query()->get()->each(function (ShopeeToken $token) use ($authService) {
            if (! $token->needsRefresh()) {
                return;
            }

            try {
                $authService->refresh($token);

                SyncLog::create([
                    'job_type' => 'shopee_token_refresh',
                    'status' => SyncStatus::Success,
                    'message' => __('Token untuk shop :shop berhasil diperbarui.', ['shop' => $token->shop_id]),
                ]);
            } catch (Throwable $e) {
                SyncLog::create([
                    'job_type' => 'shopee_token_refresh',
                    'status' => SyncStatus::Failed,
                    'message' => $e->getMessage(),
                ]);
            }
        });
    }
}
