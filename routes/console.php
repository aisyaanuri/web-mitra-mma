<?php

use App\Jobs\RefreshShopeeTokenJob;
use App\Jobs\RunReconciliationJob;
use App\Jobs\SyncShopeeLogisticsJob;
use App\Jobs\SyncShopeeOrdersJob;
use App\Jobs\SyncShopeeReturnsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new RunReconciliationJob)->hourly();

Schedule::job(new SyncShopeeOrdersJob)->everyFiveMinutes();
Schedule::job(new SyncShopeeLogisticsJob)->everyTenMinutes();
Schedule::job(new SyncShopeeReturnsJob)->everyThirtyMinutes();
Schedule::job(new RefreshShopeeTokenJob)->hourly();
