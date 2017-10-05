<?php
/**
 * Dashboard offer post controller, it uses the same base controller as cms
 * Shared by all domains.
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Models\Offer;

class OfferController extends CmsController
{
    /* Columns to be retrieved for posts list */
    private $offersColumns = ['offers.id', 'author_id',
        'channel_id', 'status',
        'title', 'published_at', 'created_at', 'updated_at'];

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
     * Move multiple offers into trash
     */
    public function deleteOffers(Request $request)
    {
        return response('API unimplemented', 401);
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
        return $this->putEntityReq($request, 'id', $id);
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
}
