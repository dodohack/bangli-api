<?php
/**
 * Affiliate Window interface
 */

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Client;


class AffiliateWindowController extends Controller
{
    private $awin_id;      // Affiliate Window ID
    private $awin_pro_api; // Affiliate Window promotion API endpoint
    private $awin_pro_id;  // Affiliate Window promotional ID
    private $awin_filters="promotionType=&categoryIds=&regionIds=&advertiserIds=&membershipStatus=joined&promotionStatus=";

    public function __construct()
    {
        // Read Affiliate Window configuration from file .env.
        $this->awin_id      = env('AWIN_ID');
        $this->awin_pro_api = env('AWIN_PROMOTION_API');
        $this->awin_pro_id  = env('AWIN_PROMOTION_ID');
    }

    public function getOffers()
    {
        $client = new Client();

        try {
            $ep = $this->awin_pro_api . '/' . $this->awin_id . '/' .
                $this->awin_pro_id . '?' . $this->awin_filters;
            $res = $client->request('GET', $ep);
        } catch (ServerException $e) {
            // TODO: handle server exception
            return false;
        }

        /* Read up to 10M data from returned body */
        return $res->getBody()->read(1024*1024*10);
    }
}
