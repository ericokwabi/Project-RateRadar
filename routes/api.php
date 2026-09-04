<?php

use App\Http\Controllers\ApiCredentialsController;
use App\Http\Controllers\LiteSpeedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
 * Alles achter de sessie van de ingelogde gebruiker. Zonder dit kan iedereen
 * die bij deze poort kan de API-secrets van webshops opvragen en aanpassen.
 */


Route::get('account/ratelimit', [LiteSpeedController::class, 'accountRatelimit'])->middleware('log.api');

Route::middleware('auth:sanctum')->group(function () {
    // Wie ben ik? Het dashboard gebruikt dit om te weten of er nog een geldige
    // sessie is, en om de naam in de header te zetten.
    Route::get('user', fn (Request $request) => $request->user()->only(['id', 'name', 'email']));

    // De reeks metingen voor het dashboard. Optioneel: ?store_id= en ?limit=
    // Route::get('account/ratelimit', [LiteSpeedController::class, 'accountRatelimit']);

    // CRUD voor de Lightspeed-sleutels per webshop.
    Route::apiResource('credentials', ApiCredentialsController::class)
        ->parameters(['credentials' => 'credential']);
});
