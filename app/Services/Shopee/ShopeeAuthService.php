<?php

namespace App\Services\Shopee;

use App\Models\ShopeeToken;

/**
 * Handles proactive Shopee token refresh (SR-13) — tokens are refreshed
 * before they expire, not reactively after a failed API call.
 */
class ShopeeAuthService
{
    public function __construct(private readonly ShopeeGatewayInterface $gateway) {}

    public function refresh(ShopeeToken $token): ShopeeToken
    {
        $result = $this->gateway->refreshToken($token->shop_id, $token->refresh_token);

        $token->update([
            'access_token' => $result['access_token'],
            'refresh_token' => $result['refresh_token'],
            'access_expires_at' => now()->addSeconds($result['expire_in']),
            'refresh_expires_at' => now()->addDays(30),
            'last_refreshed_at' => now(),
        ]);

        return $token;
    }
}
