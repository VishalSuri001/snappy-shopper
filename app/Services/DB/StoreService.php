<?php

namespace App\Services\DB;
use App\Models\ShoppingStore;
use Illuminate\Support\Facades\DB;

class StoreService
{
    public function createStore($data)
    {
        $store = new ShoppingStore($data);
        $store->save();
        return $store;
    }

    public function getNearbyStores($latitude, $longitude, $distance)
    {
        return DB::table('shopping_stores')
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
    }

    public function getDeliverableStoresForCoordinates($latitude, $longitude, $deliveryRadiusInMeters)
    {
        return ShoppingStore::select('*', DB::raw("
            ST_Distance_Sphere(
                point(longitude, latitude),
                point(?, ?)
            ) AS distance
        "))
            ->having('distance', '<=', $deliveryRadiusInMeters)
            ->setBindings([$longitude, $latitude])
            ->get();
    }
}
