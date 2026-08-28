<?php

namespace App\Http\Controllers;

use App\Models\ApiCredential;
use App\Services\LiteSpeedService;
use App\Services\RateLimitSampler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiteSpeedController extends Controller
{
    /** Zoveel metingen gaan er maximaal mee terug naar het dashboard. */
    private const DEFAULT_HISTORY = 500;

    public function index(LiteSpeedService $api): View
    {
        return view('items.index', ['items' => $api->getLiteSpeedEndpoint()]);
    }

    /**
     * De bewaarde reeks metingen voor het dashboard.
     *
     * Deze route meet zelf niets. Het vastleggen gebeurt uitsluitend door de
     * geplande taak (`ratelimit:sample`, elke vijf minuten), zodat de reeks
     * even dicht blijft of er nu iemand kijkt of niet. Zou het dashboard bij
     * elke verversing zelf meten, dan verbruikt RateRadar juist het limiet dat
     * het hoort te bewaken -- en dan vertelt de grafiek meer over het kijkgedrag
     * dan over de webshop.
     */
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

        $measurements = $sampler->history($credential, $validated['limit'] ?? self::DEFAULT_HISTORY);

        return response()->json([
            'data' => $measurements,
            'meta' => [
                'store_id' => $credential?->store_id,
                'count' => count($measurements),
            ],
        ]);
    }
}
