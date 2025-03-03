<?php

namespace App\Services;

use App\Models\Delivery;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

class NearestDeliveryService
{
  public function __construct(public Request $request)
  {
  }

  public function getNearestDelivery()
  {
    try {
      $user = $this->request->attributes->get('firebaseUser');
      
      // Use a geocoding API or a database to find the nearest delivery location
      $distances = [];
      Delivery::lazy()->each(function ($representative) use ($user, &$distances) {
        $distance = $this->calculateDistance($representative->latitude, $representative->longitude, $user->latitude, $user->longitude);
        $distances[] = ['representative_id' => $representative->id, 'distance' => $distance];
      });

      $sortedArray = Arr::sort($distances, function ($item) {
        return $item['distance'];
      });

      $sortedArray = array_values($sortedArray);

      if (count($sortedArray) > 0) {
        $delivery = Delivery::find($sortedArray[0]['representative_id']);
        return response()->json(['nearestDelivery' => $delivery]);
      }
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  public function calculateDistance($lat1, $lon1, $lat2, $lon2)
  {
    $earthRadius = 6371;

    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    $dlat = $lat2 - $lat1;
    $dlon = $lon2 - $lon1;

    $a = sin($dlat / 2) * sin($dlat / 2) +
      cos($lat1) * cos($lat2) *
      sin($dlon / 2) * sin($dlon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c; // Distance in km
  }
}


