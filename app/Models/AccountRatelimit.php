<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Een gemeten stand van één limietvenster op één moment.
 *
 * @property int $id
 * @property int|null $api_credential_id
 * @property Carbon|null $measured_at
 * @property string|null $measurement_id
 * @property string $limit_type
 * @property int $limit
 * @property int $remaining
 * @property bool $hit_429
 * @property int $reset
 * @property Carbon|null $reset_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int $used
 */
class AccountRatelimit extends Model
{
    /**
     * De sleutels zoals Lightspeed ze noemt, vertaald naar de namen die het
     * dashboard gebruikt. Zie contract.ts in de frontend.
     */
    public const WINDOWS = [
        'limit5Min' => '5min',
        'limitHour' => '1hour',
        'limitDay' => '24hour',
    ];

    protected $fillable = [
        'api_credential_id',
        'measured_at',
        'measurement_id',
        'limit_type',
        'limit',
        'remaining',
        'hit_429',
        'reset',
        'reset_time',
    ];

    protected function casts(): array
    {
        return [
            'measured_at' => 'datetime',
            'reset_time' => 'datetime',
            'limit' => 'integer',
            'remaining' => 'integer',
            'reset' => 'integer',
            'hit_429' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<ApiCredential, $this>
     */
    public function credential(): BelongsTo
    {
        return $this->belongsTo(ApiCredential::class, 'api_credential_id');
    }

    /** Aantal calls dat binnen dit venster al verbruikt is. */
    public function getUsedAttribute(): int
    {
        return max(0, $this->limit - $this->remaining);
    }

    /**
     * Filtert op één webshop; null betekent de sleutel uit .env.
     *
     * @param  Builder<AccountRatelimit>  $query
     * @return Builder<AccountRatelimit>
     */
    public function scopeForCredential(Builder $query, ?int $credentialId): Builder
    {
        return $credentialId === null
            ? $query->whereNull('api_credential_id')
            : $query->where('api_credential_id', $credentialId);
    }
}
