<?php

namespace App\Http\Controllers;

use App\Services\LiteSpeedService;
use App\Models\AccountRatelimit;

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
}
