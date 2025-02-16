<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                $respond = [
                    'status' => 404,
                    'message' => 'User not found'
                ];
                return response()->json($respond, 404);
            }
        } catch (TokenExpiredException $e) {
            $respond = [
                'status' => 401,
                'message' => 'Token is expired'
            ];
            return response()->json($respond, 401);
        } catch (TokenInvalidException $e) {
            $respond = [
                'status' => 401,
                'message' => 'Token is invalid'
            ];
            return response()->json($respond, 401);
        } catch (JWTException $e) {
            $respond = [
                'status' => 401,
                'message' => 'Token is absent'
            ];
            return response()->json($respond, 401);
        }

        return $next($request);
    }
}
