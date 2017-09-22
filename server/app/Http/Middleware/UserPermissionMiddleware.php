<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class UserPermissionMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        if (!$token = $this->auth->setRequest($request)->getToken()) {
            return response('Unauthorized.', 401);
        }

        try {
            $user = $this->auth->authenticate($token);
        } catch (TokenExpiredException $e) {
            return response('Token Expired.', 401);
        } catch (JWTException $e) {
            return response('Token Invalid.', 401);
        }

        if (!$user) {
            return response('Unauthorized.', 401);
        }

        if (!$user->can(explode('|', $permission))) {
            return response('Unauthorized', 401);
        }


        return $next($request);
    }
}
