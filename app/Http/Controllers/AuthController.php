<?php

namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use App\Helpers\JwtHelper;
use Illuminate\Http\Request;
use App\Services\TwilioSmsService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Kreait\Laravel\Firebase\Facades\Firebase;

class AuthController extends Controller
{
  public function __construct(
    private TwilioSmsService $twilio,
    private string $key = ''
  ) {
    $this->key ??= config('app.jwt_secret');
  }

  public function register(Request $request)
  {
    try {
      $rules = [
        'name' => 'required|string',
        'phone' => 'required|string',
        'device_token' => 'required|string',
        'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'latitude' => 'required|string',
        'longitude' => 'required|string',
        'password' => [
          'required',
          'string',
          Password::min(8)
            ->mixedCase()  // Requires at least one uppercase and one lowercase letter
            ->letters()
            ->numbers()
            ->symbols()
        ],
      ];
      $validator = Validator::make($request->all(), $rules);

      if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
      }

      $user = new User();
      $user->name = $request['name'];
      $user->phone = $request['phone'];
      $user->password = Hash::make($request['password']);
      $user->device_token = $request['device_token'];
      $user->latitude = $request['latitude'];
      $user->longitude = $request['longitude'];

      # store user thumbnail 
      if ($request->hasFile('thumbnail')) {
        $imagePath = $request->file('thumbnail')->store("images/user-thumbnail/{$user->id}", 'public');
      }

      $user->thumbnail = $imagePath;
      $user->save();

      # subscribe new user to the notification topic
      Firebase::messaging()->subscribeToTopic('global_notifications', $user->device_token);

      return response()->json(['message' => 'User registered successfully!', 'user' => $user]);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }

  }

  // ************************************************************************************************

  public function login(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'phone' => 'required|string',
      'password' => 'required'
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $user = User::where('phone', $request->phone)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    $payload = [
      'sub' => $user->id,   // Subject: User ID
      'phone' => $user->phone,
      'iat' => time(),       // Issued At
      'exp' => time() + ( 3600 * 24 ) // Expiration (1 day)
    ];
    $token = JwtHelper::encode($payload);

    if ($user->user_verified_at == null) {
      $verificationStatus = 'unverified';
    } else {
      $verificationStatus = 'verified';
    }
    return response()->json([
      'verificationStatus' => $verificationStatus,
      'access_token' => $token
    ], 200);
  }

  // ************************************************************************************************

  public function profile(Request $request)
  {
    try {
      $user = $request->attributes->get('jwtUser');
      return response()->json(['user' => $user]);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  // ************************************************************************************************

  public function verifyPhoneOtp(Request $request)
  {
    try {
      // getting user info 
      $user = $request->attributes->get('jwtUser');

      // sending otp information
      if ($user->user_verified_at == null) {
        # sending OTP && Store it in cache for 10 minutes 
        $phoneNumber = $user->phone;

        $otp = rand(100000, 999999);
        Cache::put('otp_' . $phoneNumber, $otp, now()->addMinutes(10));
        $message = "Your OTP is: $otp. It will expire in 10 minutes.";

        $this->twilio->sendSms($phoneNumber, $message);

        return response()->json(['message' => 'SMS sent successfully!']);
      } else {
        return response()->json(['message' => 'User already verified'], 400);
      }
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  // ************************************************************************************************


  public function verifyPhoneCheck(Request $request)
  {
    try {
      // Retrieve the authenticated user (ensure it's not null)
      $user = $request->attributes->get('jwtUser');

      // Validate request
      $validator = Validator::make($request->all(), [
        'otp' => 'required|string'
      ]);

      if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
      }

      // Retrieve OTP from cache
      $storedOtp = Cache::get('otp_' . $user->phone);

      if (!$storedOtp) {
        return response()->json(['message' => 'OTP expired or invalid'], 400);
      }

      if ($storedOtp === $request->otp) {
        // Mark user as verified if not already
        if (is_null($user->user_verified_at)) {
          $user->user_verified_at = now();
          $user->save();
        }

        // Remove OTP from cache
        Cache::forget('otp_' . $user->phone);

        return response()->json(['message' => 'OTP verified successfully!'], 200);
      } else {
        return response()->json(['message' => 'Invalid OTP'], 400);
      }

    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }
}
