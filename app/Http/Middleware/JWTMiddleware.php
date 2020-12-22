<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use http\Client\Curl\User;
use Illuminate\Contracts\Auth\Factory as Auth;

class JWTMiddleware extends Controller
{
    /***
     * @param $request
     * @param Closure $next
     * @param null $guard
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->get('token');

        if (!$token)
        {
            return $this->BuildResponse(false, "Token notfound!", $token, 401);
        }

        try {
            $credentials = JWT::decode($token, $this->JWT_SCRET(), ['HS256']);
        } catch (ExpiredException $e)
        {
            return $this->BuildResponse(false, "Token is expired!", $token, 400);
        }

        $user = User::where('email', $credentials->sub)->first();

        $request->auth = $user;
        return $next($request);
    }
}
