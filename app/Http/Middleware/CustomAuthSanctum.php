<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomAuthSanctum
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return app('auth')->guard('sanctum')->authenticate($request);
        } catch (AuthenticationException $e) {
            return response()->json([
                'message' => 'Authentication failed. Please provide a valid token.',
            ], 401);
        }
    }
}
