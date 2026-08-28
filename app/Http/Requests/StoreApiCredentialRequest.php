<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApiCredentialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Dezelfde regels als het formulier in de frontend, zodat een gebruiker
     * niet twee verschillende verhalen te horen krijgt.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'string', 'max:255', 'regex:/^[\w-]+$/', Rule::unique('api_credentials', 'store_id')],
            'api_key' => ['required', 'string', 'max:255'],
            'api_secret' => ['required', 'string', 'min:8', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'store_id.regex' => 'Store ID mag alleen letters, cijfers, - en _ bevatten.',
            'store_id.unique' => 'Voor deze webshop staan al credentials opgeslagen.',
            'api_secret.min' => 'API secret lijkt te kort — verwacht minstens 8 tekens.',
        ];
    }
}
