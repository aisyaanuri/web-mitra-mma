<?php

namespace App\Services\Shopee;

use DateTimeInterface;

/**
 * Abstraction over the Shopee Open Platform API. Two implementations exist:
 * FakeShopeeGateway (generated fixture data, used while the client's real
 * Shopee Partner/ISV credentials are pending) and ShopeeApiGateway (real
 * HTTP calls). Consumers (sync jobs) depend only on this interface, so
 * switching config('shopee.mode') from "fake" to "live" requires no
 * changes anywhere else.
 */
interface ShopeeGatewayInterface
{
    /**
     * @return array<int, array{order_sn: string, order_status: string}>
     */
    public function getOrderList(DateTimeInterface $since, DateTimeInterface $until): array;

    /**
     * @return array{order_sn: string, order_status: string, buyer_username: ?string, total_amount: float, currency: string, create_time: int, item_list: array<int, array{item_id: int|string, item_name: string, model_sku: ?string, model_quantity_purchased: int, model_discounted_price: float}>}
     */
    public function getOrderDetail(string $orderSn): array;

    /**
     * @return array{order_sn: string, tracking_number: ?string, logistics_status: string, shipping_carrier: ?string}
     */
    public function getTrackingInfo(string $orderSn): array;

    /**
     * @return array<int, array{return_sn: string, order_sn: string, reason: ?string, status: string, refund_amount: float}>
     */
    public function getReturnList(DateTimeInterface $since, DateTimeInterface $until): array;

    /**
     * @return array{access_token: string, refresh_token: string, expire_in: int}
     */
    public function refreshToken(string $shopId, string $refreshToken): array;
}
