<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Helpers\JwtHelper;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthMiddleware
{
  public function handle(Request $request, Closure $next): Response
  {
      $token = $request->bearerToken();
      $decoded = JwtHelper::decode($token);
      $user = User::find($decoded->sub);

      if (!$decoded) {
          return response()->json(['message' => 'Unauthorized: Invalid token'], 401);
      }

      $request->attributes->set('jwtUser', $user);
      return $next($request);
  }
}
