<?php

namespace App\Http\Controllers;

use App\Services\LiteSpeedService;
use App\Models\AccountRatelimit;
use App\Models\ApiCredential;

class LiteSpeedController extends Controller
{
    public function index(LiteSpeedService $api)
    {
        $data = $api->getLiteSpeedEndpoint();
        foreach ($data['accountRatelimit'] as $category => $item) {
            AccountRatelimit::create(
                [
                    'limit_type' => $category,
                    'limit' => $item['limit'],
                    'remaining' => $item['remaining'],
                    'reset' => $item['reset'],
                    'reset_time' => now()->addSeconds($item['resetTime']),
                ]
            );
        }


        return view('items.index', ['items' => $data]);
    }

    public function accountRatelimit(LiteSpeedService $api)
    {
        $data = $api->getLiteSpeedEndpoint();
        $accountRatelimits = $data['accountRatelimit'];
        $apiCredentials = ApiCredential::all();

        $limits = [];
        foreach (['limit5Min', 'limitHour', 'limitDay'] as $window) {
            foreach ($apiCredentials as $credential) {
                if ($credential->api_key === config('services.litespeed.api_key')) {
                    $limits[$window]['api_key'] = $credential->api_key;
                    $limits[$window]['store_id'] = $credential->store_id;
                    break;
                }
            }
            $limits[$window] = [
                'used' => $accountRatelimits[$window]['limit'] - $accountRatelimits[$window]['remaining'],
                'limit' => $accountRatelimits[$window]['limit'],
                'hit_429' => $accountRatelimits[$window]['remaining'] === 0,
                'start_date' => now()->toIso8601String(),
                'end_date' => $accountRatelimits[$window]['resetTime'],
            ];
        }

        return response()->json([
            'timestamp' => now()->toIso8601String(),
            'limits' => $limits,
        ]);
    }
}
