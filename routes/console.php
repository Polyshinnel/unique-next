<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('catalog:import-products')
    ->cron('10 */2 * * *')
    ->withoutOverlapping()
    ->environments(['production'])
    ->onOneServer()
    ->runInBackground();

Schedule::command('catalog:update-existing-products')
    ->cron('5,35 * * * *')
    ->withoutOverlapping()
    ->environments(['production'])
    ->onOneServer()
    ->runInBackground();

Schedule::command('catalog:update-revision-products')
    ->cron('20 * * * *')
    ->withoutOverlapping()
    ->environments(['production'])
    ->onOneServer()
    ->runInBackground();

Schedule::command('seo:generate-sitemap')
    ->cron('40 */12 * * *')
    ->withoutOverlapping()
    ->environments(['production'])
    ->onOneServer()
    ->runInBackground();
