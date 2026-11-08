<?php

namespace Tests\Unit\Jobs;

use App\Jobs\RefreshShopeeTokenJob;
use App\Models\ShopeeToken;
use App\Services\Shopee\ShopeeAuthService;
use App\Services\Shopee\ShopeeGatewayInterface;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefreshShopeeTokenJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_refreshes_a_token_that_is_close_to_expiring(): void
    {
        $token = ShopeeToken::factory()->create([
            'access_token' => 'old-access-token',
            'refresh_token' => 'old-refresh-token',
            'access_expires_at' => now()->subMinute(),
        ]);

        $this->bindFakeGateway();

        $this->app->make(RefreshShopeeTokenJob::class)->handle($this->app->make(ShopeeAuthService::class));

        $token->refresh();
        $this->assertSame('new-access-token', $token->access_token);
        $this->assertSame('new-refresh-token', $token->refresh_token);
        $this->assertTrue($token->access_expires_at->isFuture());
        $this->assertNotNull($token->last_refreshed_at);

        $this->assertDatabaseHas('sync_logs', ['job_type' => 'shopee_token_refresh', 'status' => 'success']);
    }

    public function test_does_not_refresh_a_token_that_is_still_valid(): void
    {
        $token = ShopeeToken::factory()->create([
            'access_token' => 'still-valid-token',
            'access_expires_at' => now()->addHours(3),
        ]);

        $this->bindFakeGateway();

        $this->app->make(RefreshShopeeTokenJob::class)->handle($this->app->make(ShopeeAuthService::class));

        $this->assertSame('still-valid-token', $token->fresh()->access_token);
        $this->assertDatabaseMissing('sync_logs', ['job_type' => 'shopee_token_refresh']);
    }

    private function bindFakeGateway(): void
    {
        $this->app->bind(ShopeeGatewayInterface::class, fn () => new class implements ShopeeGatewayInterface
        {
            public function getOrderList(DateTimeInterface $since, DateTimeInterface $until): array
            {
                return [];
            }

            public function getOrderDetail(string $orderSn): array
            {
                return [];
            }

            public function getTrackingInfo(string $orderSn): array
            {
                return [];
            }

            public function getReturnList(DateTimeInterface $since, DateTimeInterface $until): array
            {
                return [];
            }

            public function refreshToken(string $shopId, string $refreshToken): array
            {
                return [
                    'access_token' => 'new-access-token',
                    'refresh_token' => 'new-refresh-token',
                    'expire_in' => 14400,
                ];
            }
        });
    }
}
