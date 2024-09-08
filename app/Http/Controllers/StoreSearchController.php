<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreSearchController extends Controller
{
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

        $stores = DB::table('shopping_stores')
            ->select('*', DB::raw("
            (3959 * acos(
                cos(radians($latitude)) * cos(radians(latitude)) *
                cos(radians(longitude) - radians($longitude)) +
                sin(radians($latitude)) * sin(radians(latitude))
            )) AS distance
        "))
            ->having('distance', '<=', $distance)
            ->orderBy('distance', 'asc')
            ->get();

        return response()->json($stores);
    }
}
