<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
