<?php

use App\Http\Controllers\LiteSpeedController;
use App\Services\LiteSpeedService;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::get('/items', [LiteSpeedController::class, 'index'])->name('items.index');

Route::get('/test-api', function (LiteSpeedService $api) {
    dd($api->getLiteSpeedEndpoint());
});

require __DIR__.'/settings.php';
