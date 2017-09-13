<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(
    ['namespace' => '\App\Http\Controllers\Frontend'], function () use ($router) {

    $router->get('/offer/{id}', 'OfferController@getOffer');
    $router->get('/offers', 'OfferController@getOffers');

});

/////////////////////////////////////////////////////////////////
// Test
$router->group(
    ['namespace' => '\App\Http\Controllers\Backend'], function () use ($router) {

    $router->get('/awin-get-offer', 'AffiliateWindowController@getOffers');
});