<?php
/**
 * Dashboard deal post controller, it uses the same base controller as cms
 * Shared by all domains.
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Models\Cms\Post;

class DealController extends CmsController
{
    /* Columns to be retrieved for posts list */
    private $dealsColumns = ['cms_deals.id', 'editor_id',
        'channel_id', 'state',
        'title', 'published_at', 'created_at', 'updated_at'];

    /* Relations to be queried with the deal/deals */
    private $dealsRelations = ['topics'];
    private $dealRelations  = ['topics'];


    /**
     * Return a list of deal posts
     */
    public function getDeals(Request $request)
    {
        return $this->getEntitiesReq($request,
            $this->dealsRelations, $this->dealsColumns);
    }

    /**
     * Update multiple deals
     */
    public function putDeals(Request $request)
    {
        return response('Deals post batch editing API unimplemented', 401);
    }

    /**
     * Move multiple deals into trash
     */
    public function deleteDeals(Request $request)
    {
        return response('API unimplemented', 401);
    }

    /**
     * Return deal states and occurrences
     */
    public function getStates(Request $request)
    {
        return $this->getEntityStates($request, 'deal_posts');
    }

    /**
     * Get a deal with it's relations
     * @param Request $request
     * @param $id - post id
     * @return string
     */
    public function getDeal(Request $request, $id)
    {
        return $this->getEntityReq($request, 'id', $id, null, 
            $this->dealRelations);
    }

    /**
     * Update deal by given id
     * @param Request $request
     * @param $id - post id to be updated
     * @return object
     */
    public function putDeal(Request $request, $id)
    {
        return $this->putEntityReq($request, 'id', $id);
    }

    /**
     * Create a new deal
     * @param Request $request
     * @return object
     */
    public function postDeal(Request $request)
    {
        return $this->postEntityReq($request);
    }

    /**
     * Move a deal to trash by id
     * @param Request $request
     * @param $id
     * @return Post
     */
    public function deleteDeal(Request $request, $id)
    {
        return $this->deleteEntityReq($request, 'id', $id);
    }
}
