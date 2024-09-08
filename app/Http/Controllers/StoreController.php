<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ShoppingStore;
use Illuminate\Validation\Rule;

class StoreController extends Controller
{
    public function addStore(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'postcode' => 'required|string|regex:/^([A-Z]{1,2}\d[A-Z\d]? \d[A-Z]{2})$/i',
            'latitude' => 'required|numeric|between:-90,90|regex:/^-?\d{1,2}\.\d{1,7}$/',
            'longitude' => 'required|numeric|between:-180,180|regex:/^-?\d{1,3}\.\d{1,7}$/',
            'is_open' => 'required|boolean',
            'store_type' => [
                'required',
                Rule::in([1, 2, 3])
            ],
            'delivery_distance' => 'required|numeric|between:0,9.90',
        ]);

        $store = new ShoppingStore($validatedData);
        $store->save();

        return response()->json(['message' => 'Store added successfully!', 'store' => $store], 201);
    }
}
