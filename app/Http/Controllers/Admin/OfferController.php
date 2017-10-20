<?php
/**
 * Dashboard offer post controller, it uses the same base controller as cms
 * Shared by all domains.
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\EntityController;
use App\Models\Topic;

class OfferController extends EntityController
{
    /* Columns to be retrieved for offers list, we need a full content  */
    private $offersColumns = ['offers.id', 'author_id', 'channel_id', 'status',
        'featured', 'title', 'tracking_url', 'display_url', 'vouchers',
        'aff_offer_id', 'starts', 'ends',
        'created_at', 'updated_at', 'published_at'];

    /* Relations to be queried with the offer/offers */
    private $offersRelations = ['topics'];
    private $offerRelations  = ['topics'];


    /**
     * Return a list of offers
     */
    public function getOffers(Request $request)
    {
        return $this->getEntitiesReq($request,
            $this->offersRelations, null, $this->offersColumns);
    }

    /**
     * Update multiple offers
     */
    public function putOffers(Request $request)
    {
        return response('Offers batch editing API unimplemented', 401);
    }

    /**
     * Move multiple offers into trash, by entity type and ids
     */
    public function deleteOffers(Request $request)
    {
        return $this->deleteEntitiesReq($request);
    }

    /**
     * Return offer statuss and occurrences
     */
    public function getStates(Request $request)
    {
        return $this->getEntityStates($request, 'offers');
    }

    /**
     * Get a offer with it's relations
     * @param Request $request
     * @param $id - post id
     * @return string
     */
    public function getOffer(Request $request, $id)
    {
        return $this->getEntityReq($request, 'id', $id, null, 
            $this->offerRelations);
    }

    /**
     * Update offer by given id
     * @param Request $request
     * @param $id - post id to be updated
     * @return object
     */
    public function putOffer(Request $request, $id)
    {
        $inputs = $request->all();
        // Set author_id for offer as indicate of manually modified.
        $inputs['author_id'] = $this->guard()->user()->id;

        // Update tracking_url automatically
        if (isset($inputs['display_url']) && isset($inputs['topics'])) {
            $tracking_url = $this->autoPutTrackingUrl(
                $inputs['display_url'], $inputs['topics'][0]);

            if ($tracking_url)
                $inputs['tracking_url'] = $tracking_url;
        }
        return $this->putEntity($inputs['etype'], $inputs, 'id', $id);
    }

    /**
     * Create a new offer
     * @param Request $request
     * @return object
     */
    public function postOffer(Request $request)
    {
        return $this->postEntityReq($request);
    }

    /**
     * Move a offer to trash by id
     * @param Request $request
     * @param $id
     * @return Post
     */
    public function deleteOffer(Request $request, $id)
    {
        return $this->deleteEntityReq($request, 'id', $id);
    }

    /**
     * Update offer's tracking_url automatically
     */
    private function autoPutTrackingUrl($display_url, $topicId)
    {
        $record = Topic::where('id', $topicId)->first(['aff_platform', 'aff_id']);
        if ($record) {
            switch ($record['aff_platform']) {
                case AWIN:
                    return 'http://www.awin1.com/cread.php?awinaffid='
                    . env('AWIN_ID') . '&awinmid=' . $record['aff_id']
                    . '&p=' . urlencode($display_url);
                case LINKSHARE:
                case WEBGAIN:
                default:
                    return false;
            }
        }

        return false;
    }
}
