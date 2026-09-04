<?php

namespace App\Http\Controllers;

use App\Models\ApiCredential;
use App\Services\RateLimitSampler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class LiteSpeedController extends Controller
{
    /**
     * Hoeveel seconden er minstens tussen twee live-metingen zit. Het dashboard
     * mag vaker verversen; het krijgt dan de bewaarde reeks terug.
     */
    private const MIN_SAMPLE_INTERVAL = 20;

    /** Zoveel metingen gaan er maximaal mee terug naar het dashboard. */
    private const DEFAULT_HISTORY = 500;

    public function index(RateLimitSampler $sampler): View
    {
        $latest = $sampler->history(null, 1)[0]['limits'] ?? [];

        $items = ['accountRatelimit' => []];

        foreach ($latest as $limit => $window) {
            $items['accountRatelimit'][$limit] = [
                'limit' => $window['limit'],
                'remaining' => $window['limit'] - $window['used'],
            ];
        }

        return view('items.index', ['items' => $items]);
    }

    public function accountRatelimit(Request $request, RateLimitSampler $sampler): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => ['nullable', 'string', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:2016'],
        ]);

        $credential = null;

        if (! empty($validated['store_id'])) {
            $credential = ApiCredential::where('store_id', $validated['store_id'])->first();

            if ($credential === null) {
                return response()->json([
                    'message' => "Onbekende webshop: geen credentials gevonden voor store_id \"{$validated['store_id']}\".",
                ], 404);
            }
        }

        $warning = null;

        try {
            $sampler->sampleIfStale($credential, self::MIN_SAMPLE_INTERVAL);
        } catch (RuntimeException $exception) {
            // Een mislukte meting mag de bewaarde geschiedenis niet wegvagen.
            $warning = $exception->getMessage();
        }

        $measurements = $sampler->history($credential, $validated['limit'] ?? self::DEFAULT_HISTORY);

        if ($measurements === [] && $warning !== null) {
            return response()->json(['message' => $warning], 502);
        }

        return response()->json([
            'data' => $measurements,
            'meta' => [
                'store_id' => $credential?->store_id,
                'count' => count($measurements),
                'warning' => $warning,
            ],
        ]);
    }
}
