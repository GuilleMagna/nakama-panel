<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('health:notify', function () {
    app(\App\Services\HealthAlerts::class)->deliverPending();
});

Schedule::command('health:notify')->everyMinute()->withoutOverlapping(10);
Schedule::command('renewals:check')->dailyAt('08:00')->withoutOverlapping(10);
Schedule::command('health:check')->everyFiveMinutes()->withoutOverlapping(15);
Schedule::command('monitor:check')->everyFifteenMinutes()->withoutOverlapping(10);
Schedule::command('queue:work database --queue=site-checks --stop-when-empty --max-time=50 --timeout=45 --tries=2')
    ->everyMinute()->withoutOverlapping(10)->runInBackground();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
