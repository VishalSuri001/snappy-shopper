<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\DB\StoreService;

class StoreSearchController extends Controller
{
    protected $storeService;

    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
    }

    public function getNearbyStores(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90|regex:/^-?\d{1,2}\.\d{1,7}$/',
            'longitude' => 'required|numeric|between:-180,180|regex:/^-?\d{1,3}\.\d{1,7}$/',
            'distance' => 'required|numeric|between:0,10.00',
        ]);

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $distance = $request->input('distance');

        $stores = $this->storeService->getNearbyStores($latitude, $longitude, $distance);

        if ($stores->isEmpty()) {
            return response()->json([
                'message' => 'No nearby stores found',
                'stores' => []
            ], 200);
        }

        return response()->json([
            'message' => 'Stores found!',
            'stores' => $stores
        ], 200);
    }
}
