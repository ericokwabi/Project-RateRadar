<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiCredential;

class ApiCredentialsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('api_credentials.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('api_credentials.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required|string',
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
        ]);

        $apiCredential = new ApiCredential();
        $apiCredential->store_id = $request->input('store_id');
        $apiCredential->api_key = $request->input('api_key');
        $apiCredential->api_secret = $request->input('api_secret');
        $apiCredential->save();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $apiCredential = ApiCredential::findOrFail($id);
        return view('api_credentials.show', compact('apiCredential'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $apiCredential = ApiCredential::findOrFail($id);
        return view('api_credentials.edit', compact('apiCredential'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'store_id' => 'required|string',
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
        ]);

        $apiCredential = ApiCredential::findOrFail($id);
        $apiCredential->store_id = $request->input('store_id');
        $apiCredential->api_key = $request->input('api_key');
        $apiCredential->api_secret = $request->input('api_secret');
        $apiCredential->save();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $apiCredential = ApiCredential::findOrFail($id);
        $apiCredential->delete();
    }
}
