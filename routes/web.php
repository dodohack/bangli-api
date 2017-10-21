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
    return 'Welcome to api.' . env('ROOT_DOMAIN') . ', nothing here!';
});

$router->group(
    ['namespace' => '\App\Http\Controllers\Frontend'], function () use ($router) {

    // Topic
    $router->get('/topics',         'TopicController@getTopics');
    $router->get('/topics/group',   'TopicController@getGroupTopics');
    $router->get('/topics/{guid}',  'TopicController@getTopic');

    // Offer
    $router->get('/offer/{id}',     'OfferController@getOffer');
    $router->get('/offers',         'OfferController@getOffers');

    // Advertisement
    $router->get('/advertises',       'AdvertiseController@getAds');
    $router->get('/advertises/{id}',  'AdvertiseController@getAd');

    /*************************************************************************
     * Batch requests - retrieve any groups of any records, this enables
     * client side to request any amount of different data within 1 request
     *************************************************************************/
    $router->get('/batch',            'BatchReqController@get');
});

//////////////////////////////////////////////////////////////////////////
// Backend admin routes
include('routes.admin.php');

/////////////////////////////////////////////////////////////////
// Test
$router->group(
    ['namespace' => '\App\Http\Controllers\Backend'], function () use ($router) {
    $router->get('/awin-get-merchants', 'AffiliateWindowController@testGetMerchants');
    $router->get('/awin-get-offers', 'AffiliateWindowController@testGetOffers');

    $router->get('/awin-update-merchants', 'AffiliateWindowController@updateMerchants');
    $router->get('/awin-update-offers', 'AffiliateWindowController@updateOffers');
});
