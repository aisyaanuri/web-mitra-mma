<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shopee Integration Mode
    |--------------------------------------------------------------------------
    |
    | "fake" uses FakeShopeeGateway (generated fixture data) so the warehouse
    | and reconciliation modules can be built/demoed before the client's
    | Shopee Partner/ISV registration is approved. Switch to "live" once
    | real partner_id/partner_key/shop credentials exist (see docs/).
    |
    */

    'mode' => env('SHOPEE_MODE', 'fake'),

    'base_url' => env('SHOPEE_BASE_URL', 'https://partner.shopeemobile.com'),

    'partner_id' => env('SHOPEE_PARTNER_ID'),

    'partner_key' => env('SHOPEE_PARTNER_KEY'),

    'redirect_url' => env('SHOPEE_REDIRECT_URL'),

];
