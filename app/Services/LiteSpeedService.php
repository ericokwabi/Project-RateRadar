<?php

namespace App\Services;

use App\Models\ApiCredential;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class LiteSpeedService
{
    /**
     * Haalt de actuele rate limits op bij Lightspeed.
     *
     * Zonder credential valt de service terug op de sleutel uit .env, zodat de
     * bestaande pagina's blijven werken zolang er nog geen webshops zijn
     * ingevoerd.
     *
     * @return array{
     *     status: int,
     *     hit_429: bool,
     *     windows: array<string, array{limit:int, remaining:int, reset:int, reset_time:?Carbon}>
     * }
     *
     * @throws RuntimeException wanneer de API niet bruikbaar antwoordt.
     */
    public function fetchRatelimit(?ApiCredential $credential = null): array
    {
        $key = $credential->api_key ?? config('services.litespeed.api_key');
        $secret = $credential->api_secret ?? config('services.litespeed.api_secret');
        $baseUrl = rtrim((string) config('services.litespeed.base_url'), '/');

        if ($key === null || $secret === null || $baseUrl === '') {
            throw new RuntimeException(
                'Geen Lightspeed-credentials ingesteld. Vul API_KEY, API_SECRET en API_BASE_URL in .env, of voeg een webshop toe.'
            );
        }

        try {
            $response = Http::withBasicAuth($key, $secret)
                ->timeout(10)
                ->acceptJson()
                // Alleen opnieuw proberen bij een netwerkstoring. Een 429
                // nogmaals aanroepen zou het probleem juist erger maken.
                ->retry(2, 250, fn ($exception) => $exception instanceof ConnectionException, throw: false)
                ->get($baseUrl.'/account/ratelimit.json');
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Geen verbinding met de Lightspeed API: '.$exception->getMessage(), previous: $exception);
        }

        if ($response->status() === 429) {
            throw new TooManyRequestsException('Lightspeed geeft HTTP 429: het limiet is bereikt.');
        }

        if ($response->status() === 401 || $response->status() === 403) {
            throw new RuntimeException('Lightspeed weigert deze sleutel (HTTP '.$response->status().'). Controleer de API key en het secret.');
        }

        if ($response->failed()) {
            throw new RuntimeException('Lightspeed gaf HTTP '.$response->status().'.');
        }

        $windows = $response->json('accountRatelimit');

        if (! is_array($windows) || $windows === []) {
            throw new RuntimeException('Onverwacht antwoord van Lightspeed: het veld accountRatelimit ontbreekt.');
        }

        return [
            'status' => $response->status(),
            'hit_429' => false,
            'windows' => $this->normalise($windows),
        ];
    }

    /**
     * Maakt van de ruwe respons één vorm waar de rest van de app op kan bouwen.
     *
     * Let op het verschil dat in de ruwe data makkelijk te verwisselen is:
     * `reset` is een aantal seconden, `resetTime` is een ISO-8601 tijdstip.
     *
     * @param  array<string, mixed>  $windows
     * @return array<string, array{limit:int, remaining:int, reset:int, reset_time:?Carbon}>
     */
    private function normalise(array $windows): array
    {
        $normalised = [];

        foreach ($windows as $name => $window) {
            if (! is_array($window) || ! isset($window['limit'], $window['remaining'])) {
                continue;
            }

            $normalised[$name] = [
                'limit' => (int) $window['limit'],
                'remaining' => (int) $window['remaining'],
                'reset' => (int) ($window['reset'] ?? 0),
                'reset_time' => isset($window['resetTime'])
                    ? Carbon::parse($window['resetTime'])
                    : null,
            ];
        }

        if ($normalised === []) {
            throw new RuntimeException('Onverwacht antwoord van Lightspeed: geen bruikbare limietvensters gevonden.');
        }

        return $normalised;
    }

    /**
     * De ruwe respons, zoals de bestaande pagina's hem verwachten.
     *
     * @return array<string, mixed>|null
     */
    public function getLiteSpeedEndpoint(?ApiCredential $credential = null): ?array
    {
        $key = $credential->api_key ?? config('services.litespeed.api_key');
        $secret = $credential->api_secret ?? config('services.litespeed.api_secret');

        return Http::withBasicAuth($key, $secret)
            ->timeout(10)
            ->get(rtrim((string) config('services.litespeed.base_url'), '/').'/account/ratelimit.json')
            ->json();
    }
}
