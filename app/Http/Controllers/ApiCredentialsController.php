<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApiCredentialRequest;
use App\Http\Requests\UpdateApiCredentialRequest;
use App\Http\Resources\ApiCredentialResource;
use App\Models\ApiCredential;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * CRUD voor de Lightspeed-credentials van een webshop.
 *
 * Geeft JSON terug, geen Blade-views: dit is de API waar het React-dashboard
 * op draait. Het secret komt er nooit uit, alleen de laatste vier tekens.
 */
class ApiCredentialsController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $credentials = ApiCredential::query()
            ->withCount(['ratelimits as hits_429' => fn ($query) => $query->where('hit_429', true)])
            ->orderBy('store_id')
            ->get();

        return ApiCredentialResource::collection($credentials);
    }

    public function store(StoreApiCredentialRequest $request): JsonResponse
    {
        $credential = ApiCredential::create($request->validated());

        return ApiCredentialResource::make($credential)
            ->response()
            ->setStatusCode(201);
    }

    public function show(ApiCredential $credential): ApiCredentialResource
    {
        return ApiCredentialResource::make(
            $credential->loadCount(['ratelimits as hits_429' => fn ($query) => $query->where('hit_429', true)])
        );
    }

    public function update(UpdateApiCredentialRequest $request, ApiCredential $credential): ApiCredentialResource
    {
        $attributes = $request->validated();

        // Een leeg secret betekent "ongewijzigd laten", niet "wissen".
        if (blank($attributes['api_secret'] ?? null)) {
            unset($attributes['api_secret']);
        }

        $credential->update($attributes);

        return ApiCredentialResource::make($credential);
    }

    public function destroy(ApiCredential $credential): JsonResponse
    {
        // De metingen blijven bestaan, maar raken hun webshop kwijt. Zo verlies
        // je geen geschiedenis door het opruimen van een sleutel.
        $credential->ratelimits()->update(['api_credential_id' => null]);
        $credential->delete();

        return response()->json(status: 204);
    }
}
