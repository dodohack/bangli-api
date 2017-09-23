<?php
/**
 * Support cross origin requests
 */

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware {
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header('Access-Control-Allow-Methods', 'HEAD, GET, OPTIONS, POST, PUT, PATCH, DELETE');
        $response->header('Access-Control-Allow-Headers', $request->header('Access-Control-Request-Headers'));
        $response->header('Access-Control-Allow-Credentials', 'true');
        $response->header('Access-Control-Allow-Origin', $request->header('Origin'));

        return $response;
    }
}