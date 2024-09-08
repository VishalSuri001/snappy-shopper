<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ShoppingStore;

class StoreDeliveryController extends Controller
{
    public function getStoresDeliveringToPostcode(Request $request)
    {
        $validatedData = $request->validate([
            'postcode' => 'required|string|regex:/^([A-Z]{1,2}\d[A-Z\d]? \d[A-Z]{2})$/i',
            'latitude' => 'required|numeric|between:-90,90|regex:/^-?\d{1,2}\.\d{1,7}$/',
            'longitude' => 'required|numeric|between:-180,180|regex:/^-?\d{1,3}\.\d{1,7}$/',
        ]);

        $postcode = $validatedData['postcode'];
        $latitude = $validatedData['latitude'];
        $longitude = $validatedData['longitude'];
        $deliveryRadiusInMeters = 00.01 * 1609.34;

        $stores = ShoppingStore::select('*', DB::raw("
            ST_Distance_Sphere(
                point(longitude, latitude),
                point(?, ?)
            ) AS distance
        "))
            ->having('distance', '<=', $deliveryRadiusInMeters)
            ->setBindings([$longitude, $latitude])
            ->get();

        return response()->json($stores);
    }
}
