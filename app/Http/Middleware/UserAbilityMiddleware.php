<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class UserAbilityMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $roles, $permissions,
                           $validateAll = false)
    {
        if (!$token = $this->auth->setRequest($request)->getToken()) {
            return response('Unauthorized.', 401);
        }

        try {
            $user = $this->auth->authenticate();
        } catch (TokenExpiredException $e) {
            return response('Token Expired.', 401);
        } catch (JWTException $e) {
            return response('Token Invalid.', 401);
        }

        if (!$user) {
            return response('Unauthorized.', 401);
        }

        if (!$user->ability(explode('|', $roles),
            explode('|', $permissions),
            array('validate_all' => $validateAll))) {
            return response('Unauthorized', 401);
        }


        return $next($request);
    }
}
