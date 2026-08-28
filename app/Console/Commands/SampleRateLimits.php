<?php

namespace App\Console\Commands;

use App\Models\ApiCredential;
use App\Services\RateLimitSampler;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Legt periodiek een meetpunt vast, ook als er niemand naar het dashboard kijkt.
 * Zonder dit bestaat de geschiedenis alleen uit momenten waarop iemand toevallig
 * de pagina open had staan.
 */
class SampleRateLimits extends Command
{
    protected $signature = 'ratelimit:sample
                            {--store= : Alleen deze webshop meten (store_id)}
                            {--delay=0 : Wacht zoveel seconden voor het meten}';

    protected $description = 'Meet de Lightspeed rate limits en bewaar het resultaat';

    public function handle(RateLimitSampler $sampler): int
    {
        // De scheduler start op de hele minuut. Met een wachttijd landt de
        // meting vlak voor de reset, waar het venster op zijn voller is --
        // precies de stand die je wilt zien.
        $delay = max(0, (int) $this->option('delay'));

        if ($delay > 0) {
            $this->line("Wachten tot vlak voor de reset ({$delay}s)...");
            sleep($delay);
        }

        $credentials = $this->targets();
        $failures = 0;

        foreach ($credentials as $credential) {
            $label = $credential?->store_id ?? '.env-sleutel';

            try {
                $sampler->sample($credential);
                $this->info("Gemeten: {$label}");
            } catch (RuntimeException $exception) {
                $this->error("Mislukt ({$label}): {$exception->getMessage()}");
                $failures++;
            }
        }

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Welke webshops meten we? Zonder ingevoerde credentials valt dit terug op
     * de sleutel uit .env, zodat het commando ook werkt voordat iemand een
     * webshop heeft toegevoegd.
     *
     * @return iterable<ApiCredential|null>
     */
    private function targets(): iterable
    {
        if ($store = $this->option('store')) {
            $credential = ApiCredential::where('store_id', $store)->first();

            if ($credential === null) {
                $this->error("Geen credentials gevonden voor store_id \"{$store}\".");

                return [];
            }

            return [$credential];
        }

        $credentials = ApiCredential::all();

        return $credentials->isEmpty() ? [null] : $credentials;
    }
}
