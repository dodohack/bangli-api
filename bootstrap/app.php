<?php

require_once __DIR__.'/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__.'/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
 * Include our constant definition
 */
require_once __DIR__.'/../app/Const.php';

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);


// FIXME: Workaround for bug introduced in lumen 5.5.15:
// https://github.com/laravel/framework/issues/21697
$app->configure('view');


/* Add customized status code config file */
$app->configure('ads');
$app->configure('auth');
$app->configure('database');
$app->configure('cache');
// Load config/filesystems.php before facade
$app->configure('filesystems');
class_alias('Illuminate\Support\Facades\Storage', 'Storage');

$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware([
    App\Http\Middleware\CorsMiddleware::class
]);

$app->routeMiddleware([
//    'auth' => App\Http\Middleware\Authenticate::class,
    'role' => App\Http\Middleware\UserRoleMiddleware::class,
    'permission' => App\Http\Middleware\UserPermissionMiddleware::class,
    'ability' => App\Http\Middleware\UserAbilityMiddleware::class
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

 $app->register(App\Providers\AppServiceProvider::class);
 $app->register(App\Providers\AuthServiceProvider::class);
 $app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
  //$app->register(App\Providers\EventServiceProvider::class);

/*
 |--------------------------------------------------------------------------
 | Register filesystem
 |--------------------------------------------------------------------------
 */
$app->singleton('filesystem', function ($app) {
    return $app->loadComponent('filesystems',
        'Illuminate\Filesystem\FilesystemServiceProvider', 'filesystem');
});


/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
