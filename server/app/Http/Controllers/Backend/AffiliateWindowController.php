<?php
/**
 * Affiliate Window API client
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
    private $awin_ads_api; // Affiliate Window advertiser metadata API
    private $awin_ads_pwd; // Affiliate Window advertiser metadata API password
    private $awin_offer_filters="promotionType=&categoryIds=&regionIds=&advertiserIds=&membershipStatus=joined&promotionStatus=";
    private $awin_ads_filters="format=CSV&filter=SUBSCRIBED_ALL";

    public function __construct()
    {
        // Read Affiliate Window configuration from file .env.
        $this->awin_id      = env('AWIN_ID');
        $this->awin_pro_api = env('AWIN_PROMOTION_API');
        $this->awin_pro_id  = env('AWIN_PROMOTION_ID');
        $this->awin_ads_api = env('AWIN_ADVERTISER_API');
        $this->awin_ads_pwd = env('AWIN_ADVERTISER_API_PWD');
    }

    /**
     *
     */
    public function updateAdvertisers()
    {
        $res = $this->getAllAdvertisers();
        if (!$res) return "TODO: FAILED TO UPDATE ADVERTISERS!";

        $this->putAdvertisers($res);
    }

    /**
     *
     */
    public function updateOffers()
    {
        $res = $this->getAllOffers();
        if (!$res) return "TODO: FAILED TO UPDATE OFFERS!";

        $this->putOffers($res);
    }

    /**
     * Return a list of advertisers' metadata we have subscribed in CSV
     */
    private function getAllAdvertisers()
    {
        $client = new Client();

        try {
            $ep = $this->awin_ads_api . '?user=' . $this->awin_id .
                '&password=' . $this->awin_ads_pwd . '&' . $this->awin_ads_filters;
            $res = $client->request('GET', $ep);
        } catch (ServerException $e) {
            // TODO: handle network exception
            return false;
        }

        // Read up to 10M data from returned stream should be enough
        return $res->getBody()->read(1024*1024*10);
    }

    /**
     * Return a list of promotions of subscribed advertisers in CSV
     * @return bool|string
     */
    private function getAllOffers()
    {
        $client = new Client();

        try {
            $ep = $this->awin_pro_api . '/' . $this->awin_id . '/' .
                $this->awin_pro_id . '?' . $this->awin_offer_filters;
            $res = $client->request('GET', $ep);
        } catch (ServerException $e) {
            // TODO: handle server exception
            return false;
        }

        // Read up to 10M data from returned stream should be enough
        return $res->getBody()->read(1024*1024*10);
    }

    /**
     * Loop the list of advertisers' metadata, store them into database
     */
    private function putAdvertisers($res)
    {
        // Explode the string into lines
        $lines = explode('\n', $res);
        foreach ($lines as $line) {
            // Convert CSV string into array
            $metadata = str_getcsv($line, ',', '"');
            $this->putAdvertiser($metadata);
        }
    }

    /**
     * Convert advertiser's metadata to the record that can be stored into db
     * The fields we need to collect from metadata are:
     * Merchant ID:   metadata[0]
     * Merchant Name: metadata[1]
     * Merchant Logo: metadata[2]
     * Merchant Active: metadata[3]
     * Merchant Desc: metadata[5]
     * Merchant Content: metadata[6]
     * Merchant Tracking URL: metadata[7]
     * Merchant Category: metadata[8]
     * Merchant Display URL: metadata[14]
     * Merchant Region: metadata[15]
     *
     */
    private function putAdvertiser(Array $metadata)
    {
        // Skip in-active merchants
        if ($metadata[3] != 'yes') return;

        $merchant = [
            'slug'   => slugfy(metadata[1]),
            'name'   => metadata[1],
            'aff_id' => metadata[0],
            'aff_platform' => 'AWIN',
            'logo'   => metadata[2],
            'description' => metadata[5],
            'content' => metadata[6],
            'tracking_url' => metadata[7],
            //'category'
            'display_url'  => metadata[14],
            'region'       => metadata[15]
        ];


    }

    private function putOffers($res)
    {

    }

    private function putOffer()
    {

    }
}
