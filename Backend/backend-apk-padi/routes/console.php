<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Otomatis sinkronkan cuaca dan sensor tanah setiap 5 menit
Schedule::command('padi:sync-weather-soil')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

