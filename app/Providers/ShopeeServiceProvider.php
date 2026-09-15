<?php

namespace App\Providers;

use App\Services\Shopee\FakeShopeeGateway;
use App\Services\Shopee\ShopeeApiGateway;
use App\Services\Shopee\ShopeeGatewayInterface;
use Illuminate\Support\ServiceProvider;

class ShopeeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ShopeeGatewayInterface::class, function () {
            if (config('shopee.mode') === 'live') {
                return new ShopeeApiGateway(
                    config('shopee.base_url'),
                    config('shopee.partner_id'),
                    config('shopee.partner_key'),
                );
            }

            return new FakeShopeeGateway;
        });
    }
}
