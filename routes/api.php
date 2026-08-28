<?php

use App\Http\Controllers\ApiCredentialsController;
use App\Http\Controllers\LiteSpeedController;
use Illuminate\Support\Facades\Route;

// De reeks metingen voor het dashboard. Optioneel: ?store_id= en ?limit=
Route::get('account/ratelimit', [LiteSpeedController::class, 'accountRatelimit']);

// CRUD voor de Lightspeed-sleutels per webshop.
Route::apiResource('credentials', ApiCredentialsController::class)
    ->parameters(['credentials' => 'credential']);
