<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ShoppingStore;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StoreDeliveryController extends Controller
{
    public function getStoresDeliveringToPostcode(Request $request)
    {

        // Manually validate the request data to handle validation errors as JSON
        $validator = Validator::make($request->all(), [
            'postcode' => 'required|string|regex:/^([A-Z]{1,2}\d[A-Z\d]? \d[A-Z]{2})$/i',
            'latitude' => 'required|numeric|between:-90,90|regex:/^-?\d{1,2}\.\d{1,7}$/',
            'longitude' => 'required|numeric|between:-180,180|regex:/^-?\d{1,3}\.\d{1,7}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();

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

        if ($stores->isEmpty()) {
            return response()->json(['message' => 'No stores delivering to this postcode', 'stores' => []], 200);
        }

        return response()->json(['message' => 'Store Found!', 'stores' => $stores]);
    }
}
