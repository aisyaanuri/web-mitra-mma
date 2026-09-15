<?php

namespace Tests\Unit\Services\Shopee;

use App\Services\Shopee\FakeShopeeGateway;
use App\Services\Shopee\ShopeeGatewayInterface;
use PHPUnit\Framework\TestCase;

class FakeShopeeGatewayTest extends TestCase
{
    public function test_get_order_list_returns_order_sn_and_status(): void
    {
        $gateway = new FakeShopeeGateway;

        $list = $gateway->getOrderList(now()->subDay(), now());

        $this->assertNotEmpty($list);
        foreach ($list as $summary) {
            $this->assertArrayHasKey('order_sn', $summary);
            $this->assertArrayHasKey('order_status', $summary);
        }
    }

    public function test_get_order_detail_returns_expected_shape(): void
    {
        $gateway = new FakeShopeeGateway;

        $detail = $gateway->getOrderDetail('TESTORDER123');

        $this->assertSame('TESTORDER123', $detail['order_sn']);
        $this->assertArrayHasKey('order_status', $detail);
        $this->assertArrayHasKey('total_amount', $detail);
        $this->assertArrayHasKey('currency', $detail);
        $this->assertArrayHasKey('create_time', $detail);
        $this->assertIsArray($detail['item_list']);
        $this->assertNotEmpty($detail['item_list']);

        foreach ($detail['item_list'] as $item) {
            $this->assertArrayHasKey('item_id', $item);
            $this->assertArrayHasKey('item_name', $item);
            $this->assertArrayHasKey('model_sku', $item);
            $this->assertArrayHasKey('model_quantity_purchased', $item);
            $this->assertArrayHasKey('model_discounted_price', $item);
        }
    }

    public function test_get_tracking_info_returns_expected_shape(): void
    {
        $gateway = new FakeShopeeGateway;

        $tracking = $gateway->getTrackingInfo('TESTORDER123');

        $this->assertSame('TESTORDER123', $tracking['order_sn']);
        $this->assertArrayHasKey('tracking_number', $tracking);
        $this->assertArrayHasKey('logistics_status', $tracking);
    }

    public function test_refresh_token_returns_new_tokens(): void
    {
        $gateway = new FakeShopeeGateway;

        $result = $gateway->refreshToken('123456', 'old-refresh-token');

        $this->assertArrayHasKey('access_token', $result);
        $this->assertArrayHasKey('refresh_token', $result);
        $this->assertArrayHasKey('expire_in', $result);
        $this->assertNotSame('old-refresh-token', $result['refresh_token']);
    }

    public function test_implements_the_gateway_interface(): void
    {
        $this->assertInstanceOf(ShopeeGatewayInterface::class, new FakeShopeeGateway);
    }
}
