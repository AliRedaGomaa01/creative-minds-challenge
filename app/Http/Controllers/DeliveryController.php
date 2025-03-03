<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeliveryController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $deliveries = Delivery::latest()->paginate(20);
    return response()->json(['deliveries' => $deliveries]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $rules = [
      'name' => 'required|string',
      'longitude' => 'required|string',
      'latitude' => 'required|string',
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 400);
    }

    $delivery = new Delivery();
    $delivery->name = $request->name;
    $delivery->longitude = $request->longitude;
    $delivery->latitude = $request->latitude;
    $delivery->save();

    return response()->json([
      'delivery' => $delivery,
      'message' => 'Delivery created successfully.'
    ], 200);
  }

  /**
   * Display the specified resource.
   */
  public function show(Delivery $delivery)
  {
    return response()->json(['delivery' => $delivery]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Delivery $delivery)
  {
    $rules = [
      'name' => 'required|string',
      'longitude' => 'required|string',
      'latitude' => 'required|string',
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 400);
    }

    $delivery->name = $request->name;
    $delivery->longitude = $request->longitude;
    $delivery->latitude = $request->latitude;
    $delivery->save();

    return response()->json([
      'delivery' => $delivery,
      'message' => 'Delivery updated successfully.'
    ], 200);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Delivery $delivery)
  {
    $delivery->delete();
    return response()->json(['message' => 'Delivery deleted successfully.'], 200);
  }
}
