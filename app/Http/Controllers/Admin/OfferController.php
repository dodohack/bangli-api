<?php
/**
 * Dashboard offer post controller, it uses the same base controller as cms
 * Shared by all domains.
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\EntityController;
use App\Models\Topic;
use App\Models\Offer;

class OfferController extends EntityController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Return a list of offers
     */
    public function getOffers(Request $request)
    {
        $ret = $this->getEntities($request->all());

        return $this->response($ret, 'get offers error');
    }

    /**
     * Update multiple offers
     */
    public function putOffers(Request $request)
    {
        return $this->error('API unimplemented');
    }

    /**
     * Move multiple offers into trash or physically delete them from trash
     */
    public function deleteOffers(Request $request)
    {
        $ids = $request->get('ids');
        $numDeleted = $this->deleteEntities($ids);
        return $this->response($numDeleted, 'trash offers error');
    }

    /**
     * Return offer statuss and occurrences
     */
    public function getStatus(Request $request)
    {
        $status = Offer::select(DB::raw('status, COUNT(*) as count'))
            ->groupBy('status')->get();

        return $this->response(['status' => $status], 'get offer status error');
    }

    /**
     * Get a offer with it's relations
     * @param Request $request
     * @param $id - post id
     * @return string
     */
    public function getOffer(Request $request, $id)
    {
        $offer = $this->getEntity('id', $id);
        return $this->response($offer, 'get offer error');
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

        // Update tracking_url automatically
        if (isset($inputs['display_url']) && isset($inputs['topics'])) {

            // Trim white space
            $inputs['display_url'] = trim($inputs['display_url']);

            // Generate tracking link
            $tracking_url = $this->buildTrackingUrl(
                $inputs['display_url'], 'deal', $inputs['topics'][0]);

            if ($tracking_url)
                $inputs['tracking_url'] = $tracking_url;

        }

        $offer = $this->putEntity($inputs, 'id', $id);

        return $this->response($offer, 'put offer error');
    }

    /**
     * Create a new offer
     * @param Request $request
     * @return object
     */
    public function postOffer(Request $request)
    {
        $inputs = $request->all();

        // Update tracking_url automatically
        if (isset($inputs['display_url']) && isset($inputs['topics'])) {
            $tracking_url = $this->buildTrackingUrl(
                $inputs['display_url'], 'deal', $inputs['topics'][0]);

            if ($tracking_url)
                $inputs['tracking_url'] = $tracking_url;
        }

        $offer = $this->postEntity($inputs);

        return $this->response($offer, 'post offer error');
    }

    /**
     * Move an offer to trash or physically delete it from trash
     * @param Request $request
     * @param $id
     * @return
     */
    public function deleteOffer(Request $request, $id)
    {
        $deleted = $this->deleteEntity('id', $id);

        return $this->response($deleted, 'trash offer error');
    }
}
