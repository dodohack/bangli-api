 <?php

/**
 * Webgain API client
 */

namespace App\Http\Controllers\Backend;

class WebgainController extends AffiliateController
{
    private $wg_id;       // Webgain caompaign ID
    private $wg_api_key;  // Webgain API access key
    private $wg_pro_api;  // Webgain offer API endpoint
    private $wg_deeplink_url; // Webgain deeplink url base

    public function __construct()
    {
        parent::__construct();
        $this->wg_id = env('WEBGAIN_CAMPAIGN_ID');
        $this->wg_api_key = env('WEBGAIN_API_KEY');
        $this->wg_pro_api = env('WEBGAIN_OFFER_API');

        assert($this->wg_id && $this->wg_api_key & $this->wg_pro_api &&
            $this->wg_deeplink_url && "Incorrect webgain setting in .env");

        $this->wg_pro_api = $this->wg_pro_api .
            '?key=' . $this->wg_api_key .
            '&campainId=' . $this->wg_id;
    }

    public function updateOffers()
    {
        $count = 0;
        $res = $this->retrieveData($this->wg_pro_api);
        if ($res) {
            // Save a copy for debug purpose
            file_put_contents('/tmp/webgain_offers.json', $res);
            // Update offer table
            $count = $this->putOffers($res);
        }

        return $count;
    }

    /**
     * @param $res - array of json object contains offer info, e.g:
    [
        {
            "id": "1201825",
            "status": "Live",
            "description": "Buy One Get One Half Price Gifts",
            "title": "Buy One Get One Half Price Gifts",
            "type": "Sale",
            "destinationURL": {
                "destination_url": "https://www.annsummers.com/gifts/offers/buy-1-get-1-half-price-gifts/",
                "default_clickThrough_url": "http://www.annsummers.com/home/"
            },
            "flag": {
                "idNetwork": "1",
                "programNetwork": "Webgains UK",
                "locale": "en_GB"
            },
            "program": {
                "name": "Ann Summers",
                "id": "8593"
            },
            "startdate": "2017-11-21 00:00:01",
            "enddate": "2017-11-21 23:59:59",
            "trackingLink": "http://track.webgains.com/click.html?wgcampaignid=161181&wgprogramid=8593&wgtarget=https://www.annsummers.com/gifts/offers/buy-1-get-1-half-price-gifts/",
            "membership_status": "10"
        },
        .....
    ]
     */
    private function putOffers($res)
    {
        // Counter of added and not added offers
        $countOk  = 0;
        $countBad = 0;

        $logOk  = fopen('/tmp/webgain_offers_not_added.log', 'w');
        $logBad = fopen('/tmp/webgain_offers_added.log', 'w');

        foreach ($res as $offer) {

            // Offer merchant id
            $offerMerchantId = $offer['program']['id'];

            //
            // Validate offer quality
            //
            // start date
            if (!$this->dateFilter($offer['starts'], true)) continue;
            // end date
            if (!$this->dateFilter($offer['ends'], false)) continue;
            // offer merchant id
            if (!$this->merchantIdFilter(WEBGAIN, $offerMerchantId)) continue;
            // offer title
            if (!$this->contentFilter($offer['title'])) continue;

            $input = [
                'channel_id' => 1,
                'status'     => 'publish',
                'published_at' => date('Y-m-d H:i:s'),
                'title'        => $offer['title'],
                'vouchers'     => $this->getVoucherCode($offer),
                'aff_offer_id' => $offer['id'],
                'starts'       => $offer['starts'],
                'ends'         => $offer['ends'],
                'display_url'  => $offer['destinationURL']['destination_url'],
                'tracking_url' => $this->getTrackingUrl($offer),
            ];

            if ($this->updateOfferInternally($input, WEBGAIN)) {
                $countOk++;
                fwrite($logOk, $offer . PHP_EOL);
            } else {
                $countBad++;
                fwrite($logBad, $offer . PHP_EOL);
            }
        }

        fwrite($logOk, 'Total: ' . $countOk);
        fwrite($logBad, 'Total: ' . $countBad);
        fclose($logOk);
        fclose($logBad);

        return $countOk;
    }

    private function getVoucherCode($offer)
    {
        $desc = $offer['description'];
        // "description": "Coupon Code: LHW919",
        if (substr($desc, 0, 11) === 'Coupon Code') {
            preg_match('/Coupon Code:[\s]+([a-zA-Z0-9]+)/', $desc, $matches);
            if (count($matches)) return $matches[0];
        }
    }

    private function getTrackingUrl($offer)
    {
        return  $this->wg_deeplink_url  .
        '?wgcampaignid=' . $this->wg_id .
        '&wgprogramid=' . $offer['program']['id'] .
        '&clickref=deal' .
        '&wgtarget=' . $offer['destinationURL']['destination_url'];
    }

}
