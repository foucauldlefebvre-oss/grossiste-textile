<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('import:toptex --stock-only')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/toptex-stock.log'));

Schedule::command('import:valento --stock-only')
    ->dailyAt('03:15')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/valento-stock.log'));

Schedule::command('import:valento --stock-only')
    ->dailyAt('05:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/valento-stock.log'));

Schedule::command('import:valento --prices-only')
    ->dailyAt('03:30')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/valento-prices.log'));

Schedule::command('import:solo --stock-only')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/solo-stock.log'));

Schedule::command('import:roly --update-stock-only')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/roly-stock.log'));

Schedule::command('quotes:expire')
    ->dailyAt('06:00')
    ->appendOutputTo(storage_path('logs/quotes-expire.log'));

Schedule::command('quotes:remind')
    ->dailyAt('09:00')
    ->appendOutputTo(storage_path('logs/quotes-remind.log'));

Schedule::command('maintenance:cleanup')
    ->dailyAt('04:00')
    ->appendOutputTo(storage_path('logs/maintenance.log'));
