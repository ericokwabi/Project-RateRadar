<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function credential(): BelongsTo
    {
        return $this->belongsTo(ApiCredential::class, 'api_credential_id');
    }

    /** Aantal calls dat binnen dit venster al verbruikt is. */
    public function getUsedAttribute(): int
    {
        return max(0, $this->limit - $this->remaining);
    }

    /** Filtert op één webshop; null betekent de sleutel uit .env. */
    public function scopeForCredential(Builder $query, ?int $credentialId): Builder
    {
        return $credentialId === null
            ? $query->whereNull('api_credential_id')
            : $query->where('api_credential_id', $credentialId);
    }
}
