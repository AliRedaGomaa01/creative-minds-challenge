<?php

namespace App\Services;

use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NearestDeliveryService
{
    public function __construct()
    {
    }

    public function getNearestDelivery(Request $request)
    {
        try {
            $user = $request->attributes->get('jwtUser');

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Get the nearest delivery representative using SQL (Haversine formula)
            $nearestDelivery = Delivery::select('*', DB::raw("
                (6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) 
                    * cos(radians(longitude) - radians(?)) 
                    + sin(radians(?)) * sin(radians(latitude))
                )) AS distance
            "))
            ->setBindings([$user->latitude, $user->longitude, $user->latitude])
            ->orderByRaw('distance ASC')
            ->first();

            if (!$nearestDelivery) {
                return response()->json(['error' => 'No delivery representatives found'], 404);
            }

            return response()->json(['nearestDelivery' => $nearestDelivery]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}