<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Elke vijf minuten een meetpunt, op :00, :05, :10, ... Hetzelfde ritme als
 * limit5Min, het kortste venster dat Lightspeed hanteert: zo hoort er bij elk
 * venster precies een meting, ook wanneer er niemand naar het dashboard kijkt.
 *
 * Draaien met: php artisan schedule:work
 */
Schedule::command('ratelimit:sample')
    ->everyFiveMinutes()
    ->withoutOverlapping();
