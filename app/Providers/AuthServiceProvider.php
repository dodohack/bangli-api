<?php

namespace App\Providers;

use Tymon\JWTAuth\Providers\AbstractServiceProvider;
use Tymon\JWTAuth\Http\Middleware\Check;
use Tymon\JWTAuth\Http\Parser\AuthHeaders;
use Tymon\JWTAuth\Http\Parser\QueryString;
use Tymon\JWTAuth\Http\Parser\InputSource;
use Tymon\JWTAuth\Http\Parser\LumenRouteParams;
use Tymon\JWTAuth\Http\Middleware\Authenticate;
use Tymon\JWTAuth\Http\Middleware\RefreshToken;
use Tymon\JWTAuth\Http\Middleware\AuthenticateAndRenew;

class AuthServiceProvider extends AbstractServiceProvider
{
    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->configure('jwt');

        $path = realpath(__DIR__.'/../../vendor/tymon/jwt-auth/config/config.php');
        $this->mergeConfigFrom($path, 'jwt');

        $this->app->routeMiddleware([
            'jwt.auth' => Authenticate::class,
            'jwt.refresh' => RefreshToken::class,
            'jwt.renew' => AuthenticateAndRenew::class,
            'jwt.check' => Check::class,
        ]);

        /* Register customProviderCreators */
        $this->app['auth']->provider('jwt', function ($app, array $config) {
            return new JwtUserProvider($app['hash'], $config['model']);
        });

        $this->extendAuthGuard();

        $this->app['tymon.jwt.parser']->setChain([
            new AuthHeaders,
            new QueryString,
            new InputSource,
            new LumenRouteParams,
        ]);
    }
}
