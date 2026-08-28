<?php

namespace App\Services;

use App\Models\AccountRatelimit;
use App\Models\ApiCredential;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Neemt metingen af bij Lightspeed en bewaart ze, zodat het dashboard een
 * geschiedenis heeft in plaats van alleen de stand van dit moment.
 */
class RateLimitSampler
{
    public function __construct(private readonly LiteSpeedService $api)
    {
    }

    /**
     * Meet nu, en schrijf de drie vensters weg onder één tijdstip.
     *
     * @throws \RuntimeException wanneer de API niet bruikbaar antwoordt.
     */
    public function sample(?ApiCredential $credential = null): Carbon
    {
        $measuredAt = Carbon::now();

        try {
            $result = $this->api->fetchRatelimit($credential);
            $windows = $result['windows'];
            $hit429 = false;
        } catch (TooManyRequestsException $exception) {
            // Een 429 is geen storing maar een meetresultaat: precies waar dit
            // dashboard voor bestaat. We leggen hem vast tegen de limieten van
            // de vorige meting, want de API geeft er zelf geen cijfers bij.
            $windows = $this->lastKnownWindows($credential);

            if ($windows === []) {
                throw $exception;
            }

            $windows = array_map(
                fn (array $window) => [...$window, 'remaining' => 0],
                $windows,
            );
            $hit429 = true;
        }

        DB::transaction(function () use ($windows, $credential, $measuredAt, $hit429) {
            foreach ($windows as $name => $window) {
                AccountRatelimit::create([
                    'api_credential_id' => $credential?->id,
                    'measured_at' => $measuredAt,
                    'limit_type' => $name,
                    'limit' => $window['limit'],
                    'remaining' => $window['remaining'],
                    'hit_429' => $hit429 || $window['remaining'] <= 0,
                    'reset' => $window['reset'],
                    'reset_time' => $window['reset_time'],
                ]);
            }
        });

        return $measuredAt;
    }

    /**
     * Meet alleen als de laatste meting oud genoeg is.
     *
     * Het dashboard ververst elke paar seconden. Zonder deze rem zou RateRadar
     * zelf het limiet opsouperen dat het hoort te bewaken.
     */
    public function sampleIfStale(?ApiCredential $credential, int $minIntervalSeconds): bool
    {
        $newest = AccountRatelimit::query()
            ->forCredential($credential?->id)
            ->max('measured_at');

        if ($newest !== null && Carbon::parse($newest)->diffInSeconds(Carbon::now()) < $minIntervalSeconds) {
            return false;
        }

        $this->sample($credential);

        return true;
    }

    /**
     * De bewaarde metingen in de vorm die het dashboard verwacht: oplopend op
     * tijd, met de vensternamen 5min / 1hour / 24hour.
     *
     * @return list<array{timestamp:string, limits:array<string, array{used:int, limit:int, hit_429:bool}>}>
     */
    public function history(?ApiCredential $credential, int $limit = 500): array
    {
        $moments = AccountRatelimit::query()
            ->forCredential($credential?->id)
            ->whereNotNull('measured_at')
            ->distinct()
            ->orderByDesc('measured_at')
            ->limit($limit)
            ->pluck('measured_at');

        if ($moments->isEmpty()) {
            return [];
        }

        $rows = AccountRatelimit::query()
            ->forCredential($credential?->id)
            ->whereIn('measured_at', $moments)
            ->orderBy('measured_at')
            ->get();

        $measurements = [];

        foreach ($rows->groupBy(fn (AccountRatelimit $row) => $row->measured_at->toIso8601String()) as $timestamp => $group) {
            $limits = [];

            foreach ($group as $row) {
                $key = AccountRatelimit::WINDOWS[$row->limit_type] ?? null;

                if ($key === null) {
                    continue;
                }

                $limits[$key] = [
                    'used' => $row->used,
                    'limit' => $row->limit,
                    'hit_429' => $row->hit_429,
                ];
            }

            // Het contract eist alle drie de vensters. Een halve meting laten
            // we liever weg dan dat de hele grafiek erop stukloopt.
            if (count($limits) !== count(AccountRatelimit::WINDOWS)) {
                continue;
            }

            $measurements[] = [
                'timestamp' => $timestamp,
                'limits' => $limits,
            ];
        }

        return $measurements;
    }

    /**
     * De limieten van de laatste geslaagde meting, om een 429 tegen af te zetten.
     *
     * @return array<string, array{limit:int, remaining:int, reset:int, reset_time:?Carbon}>
     */
    private function lastKnownWindows(?ApiCredential $credential): array
    {
        $newest = AccountRatelimit::query()
            ->forCredential($credential?->id)
            ->max('measured_at');

        if ($newest === null) {
            return [];
        }

        return AccountRatelimit::query()
            ->forCredential($credential?->id)
            ->where('measured_at', $newest)
            ->get()
            ->mapWithKeys(fn (AccountRatelimit $row) => [
                $row->limit_type => [
                    'limit' => $row->limit,
                    'remaining' => $row->remaining,
                    'reset' => $row->reset,
                    'reset_time' => $row->reset_time,
                ],
            ])
            ->all();
    }
}
