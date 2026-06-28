<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('catalog:import-products')
    ->daily()
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();

Schedule::command('catalog:update-revision-products')
    ->everyFourHours()
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();
