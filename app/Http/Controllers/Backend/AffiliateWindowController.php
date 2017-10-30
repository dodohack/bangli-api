<?php
/**
 * Affiliate Window API client
 */

namespace App\Http\Controllers\Backend;

use Illuminate\Support\Facades\Storage;

use App\Models\Category;
use App\Models\Topic;
use App\Models\Offer;


class AffiliateWindowController extends AffiliateController
{
    private $awin_id;      // Affiliate Window ID
    private $awin_pro_api; // Affiliate Window promotion API endpoint
    private $awin_pro_id;  // Affiliate Window promotional ID
    private $awin_ads_api; // Affiliate Window advertiser metadata API
    private $awin_ads_pwd; // Affiliate Window advertiser metadata API password

    public function __construct()
    {
        parent::__construct();
        // Read Affiliate Window configuration from file .env.
        $this->awin_id      = env('AWIN_ID');
        $this->awin_pro_id  = env('AWIN_PROMOTION_ID');
        $this->awin_ads_pwd = env('AWIN_ADVERTISER_API_PWD');

        // TODO: Get offers from all merchants even we are not joined.
        $this->awin_pro_api = env('AWIN_PROMOTION_API') . '/' .
            $this->awin_id . '/' . $this->awin_pro_id .
            '?promotionType=&categoryIds=&regionIds=&advertiserIds=&membershipStatus=joined&promotionStatus=';

        $this->awin_ads_api = env('AWIN_ADVERTISER_API') . '?user=' .
            $this->awin_id . '&password=' . $this->awin_ads_pwd .
            '&format=CSV&filter=SUBSCRIBED_ALL';
    }

    /**
     * Retrieve and update merchants info
     */
    public function updateMerchants()
    {
        $count = 0;

        $res = $this->retrieveData($this->awin_ads_api);
        if ($res) {
            // Save a copy for debug purpose
            Storage::disk('local')->put('awin_merchants.xls', $res);
            // Update database
            $count = $this->putMerchants($res);
        }

        return response($count);
    }

    /**
     * Retrieve and update offers info
     */
    public function updateOffers()
    {
        $count = 0;
        $res = $this->retrieveData($this->awin_pro_api);
        if ($res) {
            // Save a copy for debug purpose
            Storage::disk('local')->put('awin_offers.xls', $res);
            // Update offer table
            $count = $this->putOffers($res);
        }

        Storage::disk('local')->put('awin_offers.log', 'offer updated: ' . $count);
    }

    /**
     * Loop the list of advertisers' metadata, store them into database
     */
    private function putMerchants($res)
    {
        $count = 0;
        // Explode the string into lines
        $lines = explode(PHP_EOL, $res);

        foreach ($lines as $line) {
            // Skip empty line
            if (!$line) continue;
            // Convert CSV string into array
            $metadata = str_getcsv($line);
            if ($this->putMerchant($metadata)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Convert advertiser's metadata to the record that can be stored into table
     * 'topic'.
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
     *
     * @param array $metadata - merchant metadata
     * @return bool  - true when record is created, otherwise false
     */
    private function putMerchant(Array $metadata)
    {
        // Skip in-active merchants
        if (count($metadata) < 15 || $metadata[3] != 'yes') return false;

        // Topic title
        $title = htmlspecialchars_decode($metadata[1]);
        // Topic guid
        $guid = $this->urlfy($metadata[1]);

        $table = new Topic;

        // Check if we can find the merchant
        $topic = $table->where('aff_id', $metadata[0])
            ->where('aff_platform', AWIN)->first();
        if (!$topic)
            $topic = $table->where('guid', $guid)
                ->orWhere('title', $title)->first();

        if ($topic) {
            //
            // For existing topic, we only update a few empty entries of it
            //

            $input = [];
            // Check for empty columns
            if (!$topic->logo)         $input['logo'] = $metadata[2];
            if (!$topic->aff_id)       $input['aff_id'] = $metadata[0];
            if (!$topic->aff_platform) $input['aff_platform'] = AWIN;
            if (!$topic->tracking_url) $input['tracking_url'] = $metadata[7];
            if (!$topic->display_url)  $input['display_url'] = $metadata[14];

            // Update tracking/display_url only when them are empty
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

                'logo' => $metadata[2],
                'aff_id' => $metadata[0],
                'aff_platform' => AWIN,
                'tracking_url' => $metadata[7],
                'display_url' => $metadata[14],

                'description' => $metadata[5],
                'content' => $metadata[6]
            ];


            // Create the entry
            $topic = $table->create($merchant);
            // Setup merchant's category
            $catId = $this->getCategoryId($metadata[8]);
            if ($topic) {
                $topic->categories()->sync([$catId]);
                return true;
            }
        }

        return false;
    }

    /**
     * Loop the list of offers received from API endpoint, store them into
     * database.
     *
     * @param $res - http reponse from API endpoint
     * @return int - number of offer updated
     */
    private function putOffers($res)
    {
        $count = 0;
        // Explode the string into lines
        $lines = explode(PHP_EOL, $res);

        foreach ($lines as $line) {
            // Skip empty line
            if (!$line) continue;
            // Convert CSV string into array
            $offer = str_getcsv($line);

            //
            // Validate offer quality
            //
            // 1. Data integrity
            if (count($offer) < 12 || !is_numeric($offer[0])) continue;
            // 2. start date
            if (!$this->dateFilter($offer[6], true)) continue;
            // 3. end date
            if (!$this->dateFilter($offer[7], false)) continue;
            // 4. offer merchant id
            if (!$this->merchantIdFilter(AWIN, $offer[2])) continue;
            // 5. offer description
            if (!$this->contentFilter($offer[5])) continue;

            // Extend offer description when it is short
            if (strlen($offer[5]) < 40) $offer[5] = $this->contentExtender($offer[5]);

            // Save the offer
            if ($this->putOffer($offer)) $count++;
        }

        return $count;
    }

    /**
     * Convert offer raw data into record and store them into table 'offers'.
     * Required field:
     * Offer ID: offer[0]
     * Advertiser ID: offer[2]
     * Offer type: offer[3]
     * Voucher code: offer[4]
     * Description: offer[5]
     * Starts: offer[6]
     * Ends: offer[7]
     * Category: offer[8] -- Maybe
     * Tracking link: offer[11]
     * Display link: offer[12]
     *
     * @return bool
     */
    private function putOffer(Array $offer)
    {
        $input = [
            'channel_id' => 1,
            // TODO: After introducing more criteria, we can safely set this to 'publish'
            'status'     => 'publish',
            'published_at' => date('Y-m-d H:i:s'),
            'title'      => htmlspecialchars_decode(substr($offer[5], 0, 1024)),
            'vouchers'   => $offer[4],
            'aff_offer_id' => $offer[0],
            'starts'     => $this->AWinDate2MySQLDate($offer[6]),
            'ends'       => $this->AWinDate2MySQLDate($offer[7]),
            'tracking_url' => $offer[11],
            'display_url'  => $offer[12]
        ];

        $topicTable = new Topic;
        $merchant = $topicTable->where('aff_id', $offer[2])
            ->where('aff_platform', AWIN)
            ->with(['categories', 'offers'])->first();

        // We may not able to find the merchant if merchant table is relative old.
        if (!$merchant) return false;

        // If we can find the same offer
        $found = false;
        // If the offer we found can be updated automatically, if it is
        // already modified by user, we will not overwrite it
        $canUpdate = false;
        $offerId = null;

        if ($merchant->offers->count()) {
            foreach($merchant->offers as $o) {
                if ($o->aff_offer_id == $offer[0]) {
                    $found = true;
                    if ($o->author_id == null) {
                        $offerId = $o->id;
                        $canUpdate = true;
                    }
                    break;
                }
            }
        }

        // Get offer table
        $table = new Offer;

        // Remove old offer we just found
        if ($canUpdate) {
            $table->where('id', $offerId)->delete();
        }

        // Create the offer
        if (!$merchant->offers->count() || !$found || ($found && $canUpdate)) {
            // There is no offers attached to the topic
            $record = $table->create($input);
            // Update the pivot table
            $record->topics()->sync([$merchant->id]);
            // FIXME: Some merchants have empty categories!
            // Update offer category
            if(count($merchant->categories))
                $record->categories()->sync([$merchant->categories[0]->id]);
        }

        return true;
    }

    private function AWinDate2MySQLDate($date)
    {
        // Awin date format: 'dd/mm/yyyy hh:mm'
        return preg_replace('#(\d{2})/(\d{2})/(\d{4})\s(.*)#', '$3-$2-$1 $4', $date);
    }

    /**
     * Map awin category to our local category
     */
    private function getCategoryId($acat)
    {
        $cat = 'other-stuff';
        $acat = strtolower($acat);
        if (strpos($acat, 'food') !== false) $cat = 'cooking'; // 厨房分类
        else if (strpos($acat, 'cloth') !== false) $cat = 'clothes-bag';
        else if (strpos($acat, 'travel') !== false) $cat = 'travel';
        else if (strpos($acat, 'gift') !== false) $cat = 'beauty';
        else if (strpos($acat, 'health') !== false) $cat = 'healthcare';
        //else if (strpos($acat, 'mobile') !== false ||
        //    strpos($acat, 'isp') !== false) $cat = 'telecom'; // 电信业务分类
        //else if (strpos($acat, 'ticket')) return '';
        //else if (strpos($acat, 'sport')) return '';

        // TODO: Redo our category!!

        return Category::where('slug', $cat)->first()->id;
    }


    /**************************************************************************
     * Test only API
     **************************************************************************/


    /**
     * Return a list of advertisers' metadata we have subscribed in CSV
     */
    public function testGetMerchants()
    {
        $res = $this->retrieveData($this->awin_ads_api);
        if (!$res)
            return response('FAIL TO GET MERCHANTS METADATA FROM AWIN');
        return response('SUCCESS');
    }

    /**
     * Return a list of promotions of subscribed advertisers in CSV
     * @return bool|string
     */
    public function testGetOffers()
    {
        $res = $this->retrieveData($this->awin_pro_api);
        if (!$res)
            return response('FAIL TO GET PROMOTIONS FROM AWIN');
        return response('SUCCESS');
    }

    public function testPostMerchant($metadata)
    {
        // Delete record before create
        $table = new Topic;
        $entity = $table->where('aff_id', $metadata[0])
            ->where('aff_platform', AWIN)->first();

        if ($entity)
            $table->where('aff_id', $metadata[0])
                ->where('aff_platform', AWIN)->delete();

        if ($this->putMerchant($metadata)) {
            return response('SUCCESS');
        }

        return response('FAIL TO CREATE MERCHANT RECORD');
    }

    public function testPostOffer($input)
    {
        $table = new Topic;
        $entity = $table->where('aff_id', $input[2])
            ->where('aff_platform', AWIN)->first();

        if ($entity) {
            if ($entity->offers->count()) {
                dd("TODO");
            } else {
                // Attach the offer
                if ($this->putOffer($input)) {
                    return response('SUCCESS');
                }

                return response('FAIL TO CREATE OFFER RECORD');
            }
        } else {
            return response('ERROR: NO CORRESPONDING TOPIC FOR THIS OFFER');
        }
    }
}
