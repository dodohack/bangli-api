<?php
/**
 * Affiliate Window interface
 */

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Http;


class AffiliateWindowController extends Controller
{
    private $awin_id;     // Affiliate Window ID
    private $awin_pro_id; // Affiliate Window promotional ID

    public function __construct()
    {
        // Read Affiliate Window configuration from file .env.
        $awin_id     = env('AWIN_ID');
        $awin_pro_id = env('AWIN_PROMOTION_ID');
        parent::__construct();
    }

    public function getOffers()
    {
        $client = new Client();

        try {
            $res = $client->request('GET', 'awin_api_end_point');
        } catch (ServerException $e) {
            // TODO: handle server exception
            return false;
        }

        /* Read up to 10M data from returned body */
        return $res->getBody()->read(1024*1024*10);
    }
}
