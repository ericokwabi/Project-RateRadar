<?php

namespace App\Console\Commands;

use App\Models\AccountRatelimit;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Gooit oude metingen weg.
 *
 * Zonder dit groeit de tabel met 864 rijen per dag per webshop -- ruim 300.000
 * per jaar -- terwijl het dashboard er nooit verder dan een week in terugkijkt.
 */
class PruneRateLimits extends Command
{
    protected $signature = 'ratelimit:prune
                            {--days=30 : Metingen ouder dan zoveel dagen verdwijnen}
                            {--dry-run : Toon wat er zou verdwijnen, verwijder niets}';

    protected $description = 'Verwijdert metingen die ouder zijn dan de bewaartermijn';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        if ($days < 1) {
            $this->error('--days moet minstens 1 zijn; anders zou je de metingen van vandaag weggooien.');

            return self::FAILURE;
        }

        $cutoff = Carbon::now()->subDays($days);

        // Rijen zonder measured_at komen van voor die kolom bestond. Die vallen
        // terug op created_at, zodat ze niet voor eeuwig blijven staan.
        $query = AccountRatelimit::query()
            ->where(fn ($rows) => $rows
                ->where('measured_at', '<', $cutoff)
                ->orWhere(fn ($old) => $old->whereNull('measured_at')->where('created_at', '<', $cutoff))
            );

        $count = (clone $query)->count();

        if ($count === 0) {
            $this->info("Niets te verwijderen; alles is jonger dan {$days} dagen.");

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("Zou {$count} rijen verwijderen van voor {$cutoff->toDateTimeString()}.");

            return self::SUCCESS;
        }

        // In brokken, zodat een achterstallige opruiming de database niet
        // minutenlang op slot zet. Eerst de sleutels ophalen en dan op id
        // verwijderen: "DELETE ... LIMIT" kent SQLite niet.
        $deleted = 0;

        do {
            $ids = (clone $query)->limit(1000)->pluck('id');
            $batch = $ids->isEmpty() ? 0 : AccountRatelimit::whereIn('id', $ids)->delete();
            $deleted += $batch;
        } while ($batch > 0);

        $this->info("{$deleted} rijen verwijderd van voor {$cutoff->toDateTimeString()}.");

        return self::SUCCESS;
    }
}
