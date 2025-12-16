<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Symfony\Component\HttpFoundation\Response;

class FirebaseAuth
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Firebase token required'
            ], 401);
        }

        $firebaseUid = $this->firebaseService->verifyIdToken($token);

        if (!$firebaseUid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Firebase token'
            ], 401);
        }

        // Add Firebase UID to request for use in controllers
        $request->merge(['firebase_uid' => $firebaseUid]);

        return $next($request);
    }
}