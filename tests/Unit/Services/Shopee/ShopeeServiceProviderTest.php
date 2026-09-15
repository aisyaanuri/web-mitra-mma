<?php

namespace Tests\Unit\Services\Shopee;

use App\Services\Shopee\FakeShopeeGateway;
use App\Services\Shopee\ShopeeApiGateway;
use App\Services\Shopee\ShopeeGatewayInterface;
use Tests\TestCase;

class ShopeeServiceProviderTest extends TestCase
{
    public function test_binds_fake_gateway_by_default(): void
    {
        config(['shopee.mode' => 'fake']);

        $this->assertInstanceOf(FakeShopeeGateway::class, $this->app->make(ShopeeGatewayInterface::class));
    }

    public function test_binds_real_gateway_when_mode_is_live(): void
    {
        config([
            'shopee.mode' => 'live',
            'shopee.base_url' => 'https://partner.test',
            'shopee.partner_id' => '12345',
            'shopee.partner_key' => 'secret',
        ]);

        $this->assertInstanceOf(ShopeeApiGateway::class, $this->app->make(ShopeeGatewayInterface::class));
    }
}
