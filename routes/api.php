<?php

use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Services\NearestDeliveryService;
use App\Http\Controllers\DeliveryController;

Route::prefix('v1')->group(function () {
  Route::get('/test' , fn()=> "test");
  Route::post('/register', [AuthController::class, 'register']);
  Route::post('/login', [AuthController::class, 'login']);
  // Notify all users
  Route::post('notify-all', fn() => (new FCMService())->sendPushNotificationToAllUsingTopic());

  Route::middleware('jwt.auth')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('/verify-phone-otp', [AuthController::class, 'verifyPhoneOtp']);
    Route::post('/verify-phone-check', [AuthController::class, 'verifyPhoneCheck']);

    // Get nearest delivery
    Route::get('/nearest-delivery', fn(Request $request) => (new NearestDeliveryService())->getNearestDelivery($request));

    Route::apiResource('delivery' , DeliveryController::class);
  });
});
