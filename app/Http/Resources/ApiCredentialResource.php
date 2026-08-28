<?php

namespace App\Http\Resources;

use App\Models\ApiCredential;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ApiCredential
 */
class ApiCredentialResource extends JsonResource
{
    /**
     * De vorm die het dashboard verwacht (zie credentials.ts in de frontend).
     *
     * Het secret zit hier bewust niet bij: alleen de laatste vier tekens, zodat
     * je een sleutel kunt herkennen zonder hem te kunnen gebruiken.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'store_id' => $this->store_id,
            'api_key' => $this->api_key,
            'api_secret_last4' => $this->api_secret_last4,
            'created_at' => $this->created_at?->toIso8601String(),
            'hits_429' => $this->whenNotNull($this->hits_429),
        ];
    }
}
