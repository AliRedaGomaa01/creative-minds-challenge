<?php

namespace App\Http\Middleware;

use Closure;
use Kreait\Firebase\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FirebaseAuthMiddleware
{
    public function __construct(private Auth $auth) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$token = $request->bearerToken()) {
            return response()->json(['message' => 'Unauthorized: No token provided'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $verifiedIdToken = $this->auth->verifyIdToken($token);
            $request->attributes->set('firebaseUser', $verifiedIdToken->claims());

            return $next($request);
        } catch (\Throwable) {
            return response()->json(['message' => 'Forbidden: Invalid token'], Response::HTTP_FORBIDDEN);
        }
    }
}
