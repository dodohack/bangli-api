<?php

/**
 * Linkshare API client
 */

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\AffiliateController;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Client;

class LinkShareController extends AffiliateController
{
    private $ls_id;        // Linkshare site ID
    private $ls_ads_api;   // Linkshare advertiser API
    private $ls_pro_api;   // Linkshare coupon API

    public function __construct()
    {
        $this->ls_id      = env('LINKSHARE_ID');
        $this->ls_ads_api = env('LINKSHARE_ADVERTISER_API');
        $this->ls_pro_api = env('LINKSHARE_COUPON_API');
    }


    public function updateMerchants()
    {
        $res = $this->getAllMerchants();
        if (!$res) return "TODO: FAILED TO UPDATE MERCHANTS!";
        $this->putMerchants($res);
    }

    public function updateOffers()
    {
        $res = $this->getAllOffers();
        if (!$res) return "TODO: FAILED TO UPDATE OFFERS!";
        $this->putOffers($res);
    }

    private function getAllMerchants()
    {
        $api = '';
        return $this->retrieveData($api);
    }

    private function getAllOffers()
    {
        $api = '';
        return $this->retrieveData($api);
    }


}