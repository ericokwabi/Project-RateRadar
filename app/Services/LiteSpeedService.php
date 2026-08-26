<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LiteSpeedService
{
    public function getLiteSpeedEndpoint()
    {
        $response = Http::withBasicAuth(config('services.litespeed.api_key'), config('services.litespeed.api_secret'))
            ->get(config('services.litespeed.base_url') . '/account/ratelimit.json');

        return $response->json();
    }
}
