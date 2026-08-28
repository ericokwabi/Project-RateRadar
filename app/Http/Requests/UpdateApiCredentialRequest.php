<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApiCredentialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Bij bewerken mag het secret leeg blijven: dat betekent "laat staan".
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'store_id' => [
                'required', 'string', 'max:255', 'regex:/^[\w-]+$/',
                Rule::unique('api_credentials', 'store_id')->ignore($this->route('credential')),
            ],
            'api_key' => ['required', 'string', 'max:255'],
            'api_secret' => ['nullable', 'string', 'min:8', 'max:255'],
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
