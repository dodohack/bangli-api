<?php

/**
 * Webgain API client
 */

namespace App\Http\Controllers\Backend;

use Illuminate\Support\Facades\Storage;

class LinkShareController extends AffiliateController
{

    public function __construct()
    {
        parent::__construct();
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
