<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * De Lightspeed-sleutels van een webshop.
 *
 * @property int $id
 * @property string $store_id
 * @property string $api_key
 * @property string $api_secret
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $api_secret_last4
 * @property-read int|null $hits_429 Alleen gevuld na een withCount() op ratelimits.
 */
class ApiCredential extends Model
{
    protected $fillable = [
        'store_id',
        'api_key',
        'api_secret',
    ];

    /**
     * Het secret gaat er wel in, maar komt er nooit uit: het staat versleuteld
     * in de database en wordt uit elke JSON-respons weggelaten. De frontend
     * krijgt alleen api_secret_last4 om een sleutel te kunnen herkennen.
     */
    protected $hidden = [
        'api_secret',
    ];

    protected function casts(): array
    {
        return [
            'api_secret' => 'encrypted',
        ];
    }

    /**
     * @return HasMany<AccountRatelimit, $this>
     */
    public function ratelimits(): HasMany
    {
        return $this->hasMany(AccountRatelimit::class);
    }

    /** De laatste vier tekens van het secret, genoeg om het te herkennen. */
    public function getApiSecretLast4Attribute(): string
    {
        $secret = (string) $this->api_secret;

        return str_pad(substr($secret, -4), 4, '•', STR_PAD_LEFT);
    }
}
