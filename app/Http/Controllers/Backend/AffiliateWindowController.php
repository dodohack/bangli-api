<?php
/**
 * Affiliate Window API client
 */

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\AffiliateController;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Client;

class AffiliateWindowController extends AffiliateController
{
    private $awin_id;      // Affiliate Window ID
    private $awin_pro_api; // Affiliate Window promotion API endpoint
    private $awin_pro_id;  // Affiliate Window promotional ID
    private $awin_ads_api; // Affiliate Window advertiser metadata API
    private $awin_ads_pwd; // Affiliate Window advertiser metadata API password

    public function __construct()
    {
        // Read Affiliate Window configuration from file .env.
        $this->awin_id      = env('AWIN_ID');
        $this->awin_pro_id  = env('AWIN_PROMOTION_ID');
        $this->awin_ads_pwd = env('AWIN_ADVERTISER_API_PWD');

        $this->awin_pro_api = env('AWIN_PROMOTION_API') . '/' .
            $this->awin_id . '/' . $this->awin_pro_id .
            '?promotionType=&categoryIds=&regionIds=&advertiserIds=&membershipStatus=joined&promotionStatus=';

        $this->awin_ads_api = env('AWIN_ADVERTISER_API') . '?user=' .
            $this->awin_id . '&password=' . $this->awin_ads_pwd .
            '&format=CSV&filter=SUBSCRIBED_ALL';
    }

    public function updateMerchants()
    {
        $count = 0;

        $res = $this->retrieveData($this->awin_ads_api);
        if ($res) {
            $count = $this->putMerchants($res);
        }

        return response($count);
    }

    public function updateOffers()
    {
        $count = 0;
        $res = $this->retrieveData($this->awin_pro_api);
        if ($res) {
            $count = $this->putOffers($res);
        }

        return response($count);
    }

    /**
     * Loop the list of advertisers' metadata, store them into database
     */
    private function putMerchants($res)
    {
        $count = 0;
        // Explode the string into lines, must be double quoted "\n".
        $lines = explode("\n", $res);

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

        // Topic guid
        $guid = $this->urlfy($metadata[1]);

        // Check if we already have the topic in the table
        $table = $this->getEntityTable(ETYPE_TOPIC);
        // Check if we have already had this merchant
        $record = $table->where('guid', $guid)->first();
        if (!$record)
            $record = $table->where('aff_id', $metadata[0])
                ->where('aff_platform', 'AWIN')->first();

        $merchantShort = array(
            'logo'   => $metadata[2],
            'aff_id' => $metadata[0],
            'aff_platform' => 'AWIN',
            'tracking_url' => $metadata[7],
            'display_url'  => $metadata[14]
        );

        $merchant = array_merge(
            [
                'guid'   => $guid,
                // TODO: Need to create an editor for auto-content
                //'editor_id' => 1,
                'title'   => $metadata[1],
                // TODO: Need to support different channels: shopping, travel
                'channel_id' => 1,
                // topic type 2: merchant
                'type_id' => 2,
                // TODO: region of the
                'location_id' => 1, // $metadata[15]
                // TODO: After introducing more criteria, we can safely set this to 'publish'
                'status' => 'publish',
                'description' => $metadata[5],
                'content' => $metadata[6]
            ],
            $merchantShort
        );

        if ($record) {
            // Update the entry only when the editor is null
            // Otherwise we don't update the topic which may be modified manually.
            if ($record->editor_id == null) {
                if ($table->where('id', $record->id)->update($merchant))
                    return true;
                else
                    return false;
            }

            // Partially update old topic with affiliate info.
            if ($record->aff_id == null) {
                if ($table->where('id', $record->id)->update($merchantShort))
                    return true;
                else
                    return false;
            }

            return false;
        } else {
            // Create the entry
            $record = $table->create($merchant);
            if (!$record)
                return false;
            else
                return true;
        }
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
        // Expolode the string into lines, must be doulb quoted "\n"
        $lines = explode("\n", $res);

        foreach ($lines as $line) {
            // Skip empty line
            if (!$line) continue;
            // Convert CSV string into array
            $offer = str_getcsv($line);
            if ($this->putOffer($offer)) {
                $count++;
            }
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
        // TODO: Skip some offer which is created long time ago
        if (count($offer) < 12 || !is_numeric($offer[0])) return false;

        $input = array(
            'channel_id' => 1,
            // TODO: After introducing more criteria, we can safely set this to 'publish'
            'status'     => 'publish',
            'title'      => substr($offer[5], 0, 1024),
            'vouchers'   => $offer[4],
            'aff_offer_id' => $offer[0],
            'starts'     => $this->AWinDate2MySQLDate($offer[6]),
            'ends'       => $this->AWinDate2MySQLDate($offer[7]),
            'tracking_url' => $offer[11],
            'display_url'  => $offer[12]
        );

        $topicTable = $this->getEntityTable(ETYPE_TOPIC);
        $merchant = $topicTable->where('aff_id', $offer[2])
            ->where('aff_platform', 'AWIN')->first();

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

        // Remove old offer
        if ($found && $canUpdate) {
            $table = $this->getEntityTable(ETYPE_OFFER);
            $table->where('id', $offerId)->delete();
        }

        // Create the offer
        if (!$merchant->offers->count() || !$found || ($found && $canUpdate)) {
            // There is no offers attached to the topic
            $table = $this->getEntityTable(ETYPE_OFFER);
            $record = $table->create($input);
            // Update the pivot table
            $record->topics()->sync([$merchant->id]);
        }

        return true;
    }

    private function AWinDate2MySQLDate($date)
    {
        // Awin date format: 'dd/mm/yyyy hh:mm'
        return preg_replace('#(\d{2})/(\d{2})/(\d{4})\s(.*)#', '$3-$2-$1 $4', $date);
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
        $table = $this->getEntityTable(ETYPE_TOPIC);
        $entity = $table->where('aff_id', $metadata[0])
            ->where('aff_platform', 'AWIN')->first();

        if ($entity)
            $table->where('aff_id', $metadata[0])
                ->where('aff_platform', 'AWIN')->delete();

        if ($this->putMerchant($metadata)) {
            return response('SUCCESS');
        }

        return response('FAIL TO CREATE MERCHANT RECORD');
    }

    public function testPostOffer($input)
    {
        $table = $this->getEntityTable(ETYPE_TOPIC);
        $entity = $table->where('aff_id', $input[2])
            ->where('aff_platform', 'AWIN')->first();

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
