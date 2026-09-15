<?php

namespace App\Services\Shopee;

use App\Models\ShopeeToken;
use Closure;
use DateTimeInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

/**
 * Real Shopee Open Platform API v2 client. Untestable until the client's
 * Partner/ISV registration is approved and real credentials exist (see
 * docs/) — implemented in good faith against Shopee's publicly documented
 * v2 signature scheme, but MUST be re-verified against Shopee's current
 * API docs during Fase 9's go-live swap, since endpoint paths/response
 * shapes can drift.
 */
class ShopeeApiGateway implements ShopeeGatewayInterface
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $partnerId,
        private readonly string $partnerKey,
    ) {}

    public function getOrderList(DateTimeInterface $since, DateTimeInterface $until): array
    {
        $response = $this->authenticatedRequest('/api/v2/order/get_order_list', [
            'time_range_field' => 'create_time',
            'time_from' => $since->getTimestamp(),
            'time_to' => $until->getTimestamp(),
            'page_size' => 50,
        ]);

        return $response['response']['order_list'] ?? [];
    }

    public function getOrderDetail(string $orderSn): array
    {
        $response = $this->authenticatedRequest('/api/v2/order/get_order_detail', [
            'order_sn_list' => $orderSn,
        ]);

        return $response['response']['order_list'][0] ?? [];
    }

    public function getTrackingInfo(string $orderSn): array
    {
        $response = $this->authenticatedRequest('/api/v2/logistics/get_tracking_info', [
            'order_sn' => $orderSn,
        ]);

        $data = $response['response'] ?? [];

        return [
            'order_sn' => $orderSn,
            'tracking_number' => $data['tracking_number'] ?? null,
            'logistics_status' => $data['logistics_status'] ?? '',
            'shipping_carrier' => $data['shipping_carrier'] ?? null,
        ];
    }

    public function getReturnList(DateTimeInterface $since, DateTimeInterface $until): array
    {
        $response = $this->authenticatedRequest('/api/v2/returns/get_return_list', [
            'create_time_from' => $since->getTimestamp(),
            'create_time_to' => $until->getTimestamp(),
        ]);

        return $response['response']['return'] ?? [];
    }

    public function refreshToken(string $shopId, string $refreshToken): array
    {
        $path = '/api/v2/auth/access_token/get';
        $timestamp = now()->getTimestamp();

        $response = $this->throttled(fn () => Http::baseUrl($this->baseUrl)
            ->post($path.'?'.http_build_query([
                'partner_id' => $this->partnerId,
                'timestamp' => $timestamp,
                'sign' => $this->sign($path, $timestamp),
            ]), [
                'refresh_token' => $refreshToken,
                'partner_id' => (int) $this->partnerId,
                'shop_id' => (int) $shopId,
            ])
            ->throw()
            ->json());

        return [
            'access_token' => $response['access_token'],
            'refresh_token' => $response['refresh_token'],
            'expire_in' => $response['expire_in'],
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function authenticatedRequest(string $path, array $query): array
    {
        $token = $this->currentToken();
        $timestamp = now()->getTimestamp();

        return $this->throttled(fn () => Http::baseUrl($this->baseUrl)
            ->get($path, array_merge($query, [
                'partner_id' => $this->partnerId,
                'timestamp' => $timestamp,
                'sign' => $this->sign($path, $timestamp, $token->access_token, $token->shop_id),
                'shop_id' => $token->shop_id,
                'access_token' => $token->access_token,
            ]))
            ->throw()
            ->json());
    }

    private function currentToken(): ShopeeToken
    {
        return ShopeeToken::query()->firstOrFail();
    }

    private function sign(string $path, int $timestamp, ?string $accessToken = null, ?string $shopId = null): string
    {
        $baseString = $this->partnerId.$path.$timestamp.$accessToken.$shopId;

        return hash_hmac('sha256', $baseString, $this->partnerKey);
    }

    /**
     * Respect Shopee's documented rate limits (SR-15) — a conservative
     * default; tune against the actual limits once live.
     */
    private function throttled(Closure $callback): mixed
    {
        return RateLimiter::attempt(
            'shopee-api-call',
            $perMinute = 60,
            $callback,
        ) ?: throw new RuntimeException('Shopee API rate limit reached, try again shortly.');
    }
}
