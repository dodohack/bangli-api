<?php

/**
 * Linkshare API client
 */

namespace App\Http\Controllers\Backend;

use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Exception\ServerException;
Use GuzzleHttp\Client;

use App\Models\Category;
use App\Models\Topic;
use App\Models\Offer;

class LinkShareController extends AffiliateController
{
    private $ls_id;        // Linkshare site ID
    private $ls_auth_token; // Token used to get api access token
    private $ls_username;  // Linkshare username
    private $ls_password;  // Linkshare passwordd
    private $ls_auth_api;  // Linkshare acess token api
    private $ls_ads_api;   // Linkshare advertiser API
    private $ls_pro_api;   // Linkshare coupon API

    private $token;        // Api access token, expires in 60 mins

    public function __construct()
    {
        parent::__construct();
        $this->ls_id       = env('LINKSHARE_ID');
        $this->ls_auth_token = env('LINKSHARE_AUTH_TOKEN');
        $this->ls_username = env('LINKSHARE_USERNAME');
        $this->ls_password = env('LINKSHARE_PASSWORD');
        $this->ls_auth_api = env('LINKSHARE_AUTH_API');
        $this->ls_ads_api  = env('LINKSHARE_ADVERTISER_API');
        $this->ls_pro_api  = env('LINKSHARE_COUPON_API');

        assert($this->ls_id  && $this->ls_auth_token && $this->ls_username
            && $this->ls_password && $this->ls_auth_api && $this->ls_ads_api
            && $this->ls_pro_api && "Incorrect linkshare setting in .env");

        // Renew token
        $this->token = $this->getAuthToken();
    }

    public function updateMerchants()
    {
        $count = 0;

        // Setup api access token
        $options = ['headers' => ['Authorization' => 'Bearer ' . $this->token]];

        $res = $this->retrieveData($this->ls_ads_api, $options);
        if ($res) {
            // Save a copy of merchant list for debug purpose
            Storage::disk('local')->put('ls_merchants.xml', $res);
            $count = $this->putMerchants($res);
        }

        Storage::disk('local')
            ->put('ls_merchants.log', 'merchants updated: ' . $count);
    }

    public function updateOffers()
    {
        // Actually offer count stored into to database
        $updatedOfferCount = 0;

        // Setup api access token
        $options = ['headers' => ['Authorization' => 'Bearer ' . $this->token]];

        // Query parameter:
        // network: 3 - UK network
        // resultsperpage: number of offers to get
        // pagenumber:
        $pageNum = 1;
        $perPage = 100;

        // Retrieve number of offers first
        $api = $this->ls_pro_api . '?network=3' . '&resultsperpage=0';
        $offerCount = $this->getOfferCount($api, $options);

        // Load offers until offer count is 0
        while ($offerCount > 0) {
            $api = $this->ls_pro_api . '?network=3' .
                '&resultsperpage=' . $perPage .
                '&pagenumber=' . $pageNum;

            $res = $this->retrieveData($api, $options);
            if ($res) {
                $filename = 'ls_offers_' . $pageNum . '.xml';
                // Save a copy for debug purpose
                Storage::disk('local')->put($filename, $res);
                // Update offer table
                $updatedOfferCount += $this->putOffers($res);
            }

            $filename = 'ls_offers_' . $pageNum . '.log';
            Storage::disk('local')->put($filename,
                'offer updated: ' . $updatedOfferCount);

            // Increase page number
            $pageNum++;

            // Deduce the count
            $offerCount -= $perPage;
        }
    }

    private function putMerchants($xmlResult)
    {
        $count = 0;
        $xmlparser = xml_parser_create();
        xml_parse_into_struct($xmlparser, $xmlResult, $results);

        /* Loop the result array decoded from xml file
         * We need complete tag MID and MERCHANTNAME only
        [3] =>
        array(4) {
            ["tag"]=>
            string(3) "MID"
            ["type"]=>
            string(8) "complete"
            ["level"]=>
            int(4)
            ["value"]=>
            string(5) "38935"
          }
        [4]=>
        array(4) {
            ["tag"]=>
            string(12) "MERCHANTNAME"
            ["type"]=>
            string(8) "complete"
            ["level"]=>
            int(4)
            ["value"]=>
            string(5) "Amara"
        }
        */
        for($i = 0; $i < count($results); $i++) {
            if ($results[$i]['tag'] != 'MID')
                continue;

            // Get merchant id and increase index
            $MID = $results[$i++]['value'];

            // Check if current tag is <merchantname>
            if ($results[$i]['tag'] != 'MERCHANTNAME')
                continue;

            // Get merchant name
            $MNAME = $results[$i]['value'];

            if ($this->putMerchant($MID, $MNAME)) $count++;
        }

        return $count;
    }

    /**
     * Update merchant topic record with merchant id and name,
     * we will get more data in the future
     * @param $mid
     * @param $mname
     */
    private function putMerchant($mid, $mname)
    {
        // Do not add filtered merchant
        if (!$this->merchantIdFilter(LINKSHARE, $mid)) return false;

        // Topic title
        $title = htmlspecialchars_decode($mname);
        // Topic guid
        $guid = $this->urlfy($mname);

        // Get topic table
        $table = new Topic;


        // Check if we have already have this merchant
        $topic = $table->where('aff_id', $mid)
            ->where('aff_platform', LINKSHARE)->first();
        if (!$topic)
            $topic = $table->where('guid', $guid)
                ->orWhere('title', $mname)->first();

        if ($topic) {
            //
            // For existing topic, we only update a few empty entries of it
            //
            $input = [];
            if (!$topic->aff_id)       $input['aff_id'] = $mid;
            if (!$topic->aff_platform) $input['aff_platform'] = LINKSHARE;
            // TODO: Can't get tracking/display_url from linkshare advertiser API
            //if (!$topic->tracking_url) $input['tracking_url'] = ?;
            //if (!$topic->display_url)  $input['display_url'] = ?;
            if (count($input)) {
                $topic->update($input);
                return true;
            }
        } else {
            //
            // Create a new record if we can't find one.
            //

            $merchant = [
                'guid' => $guid,
                // TODO: Need to create an editor for auto-content
                //'editor_id' => 1,
                'title' => $title,
                // TODO: Need to support different channels: shopping, travel
                'channel_id' => 1,
                // topic type 2: merchant
                'type_id' => 2,
                // TODO: region of the
                'location_id' => 1, // $metadata[15]

                // Auto created topic should be set to draft
                'status' => 'draft',

                'aff_id' => $mid,
                'aff_platform' => LINKSHARE,
            ];

            // Create the entry
            $topic = $table->create($merchant);
            if ($topic)
                return true;
        }

        return false;
    }

    private function putOffers($xml)
    {
        $count = 0;

        $xmlparser = xml_parser_create();
        xml_parse_into_struct($xmlparser, $xml, $results);
        /* Loop the result array decoded from xml file like below
        <link type="TEXT">
        <categories>
        <category id="16">Gifts</category>
        <category id="21">Jewelry &amp; Accessories</category>
        </categories>
        <promotiontypes>
        <promotiontype id="11">Percentage off</promotiontype>
        </promotiontypes>
        <offerdescription>50% Off Gifts!</offerdescription>
        <offerstartdate>2014-06-17</offerstartdate>
        <offerenddate>2020-06-16</offerenddate>
        <clickurl>https://click.linksynergy.com/fs-bin/click?id=h0MdGqh4XZ4&amp;offerid=386491.29&amp;type=3&amp;subid=0</clickurl>
        <impressionpixel>https://ad.linksynergy.com/fs-bin/show?id=h0MdGqh4XZ4&amp;bids=386491.29&amp;type=3&amp;subid=0</impressionpixel>
        <advertiserid>39380</advertiserid>
        <advertisername>H Samuel</advertisername>
        <network id="3">UK Network</network>
        </link>
         */
        for($i = 0; $i < count($results); $i++) {
            $offer = [];

            // Looking for offer description
            if ($results[$i]['tag'] != 'OFFERDESCRIPTION') continue;
            $offer['title'] = $results[$i]['value'];

            // Current xml level, all the date should be in the same level
            $level = $results[$i++]['level'];

            // Now process the same level data
            while ($results[$i]['level'] == $level) {
                switch ($results[$i]['tag']) {

                    case 'OFFERSTARTDATE':  // Offer start date
                        $offer['starts'] = $results[$i]['value'] . ' 00:00:00';
                        break;

                    case 'OFFERENDDATE': // Offer end date
                        $offer['ends'] = $results[$i]['value'] . ' 23:59:59';
                        break;

                    case 'CLICKURL': // Tracking url
                        $offer['tracking_url'] = $results[$i]['value'];
                        break;

                    case 'ADVERTISERID': // Merchant ID
                        $offer['aff_id'] = $results[$i]['value'];
                        break;

                    case 'COUPONCODE':
                        $offer['vouchers'] = $results[$i]['value'];
                        break;

                    default:
                        break;
                }

                $i++;
            }

            //
            // Validate offer quality
            //
            // 1. start date
            if (!$this->dateFilter($offer['starts'], true)) continue;
            // 2. end date
            if (!$this->dateFilter($offer['ends'], false)) continue;
            // 3. offer merchant id
            if (!$this->merchantIdFilter(LINKSHARE, $offer['aff_id'])) continue;
            // 4. offer description
            if (!$this->contentFilter($offer['title'])) continue;

            // Save the offer
            if ($this->putOffer($offer)) $count++;
        }

        return $count;
    }

    /**
     * Update single offer
     * @param array $offer
     */
    private function putOffer(Array $offer)
    {
        if (!isset($offer['aff_id'])) return false;

        $aff_id = $offer['aff_id'];
        unset($offer['aff_id']);

        $input = array_merge($offer, [
            'channel_id'   => 1,
            'status'       => 'publish',
            'published_at' => date('Y-m-d H:i:s'),
        ]);

        $topic = new Topic;
        $merchant = $topic->where('aff_id', $aff_id)
            ->where('aff_platform', LINKSHARE)
            ->with(['categories', 'offers'])->first();

        if (!$merchant) return false;

        // If we can find the same offer
        // Linkshare doesn't provide offer id, we use tracking url to identical
        // if the offers are same
        $found = false;
        if ($merchant->offers->count()) {
            foreach($merchant->offers as $o) {
                if ($o->tracking_url == $offer['tracking_url'] &&
                    $o->starts == $offer['starts'] &&
                    $o->ends == $offer['ends']) {
                    $found = true;
                    break;
                }
            }
        }

        // Do not update the same offer
        if ($found) return false;

        // Get offer table
        $table = new Offer;
        $record = $table->create($input);
        if (!$record) return false;

        // Update the pivot table
        $record->topics()->sync($merchant->id);
        // FIXME: Some merchants has empty categories
        if (count($merchant->categories))
            $record->categories()->sync([$merchant->categories[0]->id]);
        return true;
    }


    /**
     * Linkshare API request authorization token expires in 60 mins, so we need
     * to renew the token before any operations.
     */
    private function getAuthToken()
    {
        // Create http client
        $client = new Client();

        try {
            $res = $client->request('POST', $this->ls_auth_api, [
                'headers' => [
                    'Authorization' => 'Basic ' . $this->ls_auth_token,
                    'Content-Type'  => 'application/x-www-form-urlencoded'
                ],
                'form_params' => [
                    'grant_type' => 'password',
                    'username'   => $this->ls_username,
                    'password'   => $this->ls_password,
                    // Publisher id is the scope
                    'scope'      => $this->ls_id
                ]
            ]);
        } catch (ServerException $e) {
            return false;
        }

        $data = json_decode($res->getBody()->read(1024));

        // Not used yet
        //$expires_in = $data->expires_in;
        //$refresh_token = $data->refresh_token;

        return $data->access_token;
    }

    private function getOfferCount($api, $options)
    {
        $res = $this->retrieveData($api, $options);
        if ($res) {
            $xmlparser = xml_parser_create();
            xml_parse_into_struct($xmlparser, $res, $results);
            for ($i = 0; $i < count($results); $i++) {
                if ($results[$i]['tag'] != 'TOTALMATCHES')
                    continue;

                return $results[$i]['value'];
            }
        }

        // Default to 1 offer so we can kick at least 1 load
        return 1;
    }
}