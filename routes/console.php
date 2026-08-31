<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Elke vijf minuten een meetpunt. Hetzelfde ritme als limit5Min, het kortste
 * venster dat Lightspeed hanteert: zo hoort er bij elk venster precies een
 * meting, ook wanneer er niemand naar het dashboard kijkt.
 *
 * De planner vuurt op :00, :05, :10, ... maar dat is net na de reset, wanneer
 * het venster op zijn leegst is. De --delay laat het commando 4:55 wachten,
 * zodat de meting op :04:55 landt -- vlak voor de volgende reset, waar de
 * stand zegt hoeveel er dat venster werkelijk verbruikt is. Dat is ook het
 * tijdstip dat contract.ts aan de frontend-kant beschrijft.
 *
 * withoutOverlapping() vangt af dat de vorige meting nog in zijn wachttijd zit
 * wanneer de volgende aan de beurt komt. De 10 minuten zijn de vervaltijd van
 * dat slot: standaard is dat 24 uur, en dan ligt het meten een dag stil wanneer
 * je schedule:work afbreekt terwijl een meting nog in zijn --delay wacht.
 *
 * Draaien met: php artisan schedule:work
 */
Schedule::command('ratelimit:sample --delay=295')
    ->everyFiveMinutes()
    ->withoutOverlapping(10);

/*
 * Eens per nacht opruimen. Het dashboard kijkt nooit verder terug dan een week;
 * alles ouder dan een maand houden we alleen nog voor de zekerheid.
 */
Schedule::command('ratelimit:prune')
    ->dailyAt('03:30')
    ->withoutOverlapping();
