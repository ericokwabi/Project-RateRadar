<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Start in minuut 04, 09, 14, ... en wacht 55 seconden. Zo valt de meting op
 * 04:55, 09:55, 14:55, ... zonder dat een slaap van bijna vijf minuten de
 * volgende geplande taak blokkeert.
 *
 * Draaien met: php artisan schedule:work
 */
Schedule::command('ratelimit:sample --delay=55')
    ->cron('4-59/5 * * * *')
    ->withoutOverlapping();
