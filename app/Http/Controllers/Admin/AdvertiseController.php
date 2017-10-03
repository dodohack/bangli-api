<?php
/**
 * Dashboard advertisement management controller
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Controllers\EntityController;
use App\Models\Advertise;

class AdvertiseController extends EntityController
{
    /**
     * Return a list of advertises
     * @param Request $request
     * @return object
     */
    public function getAdvertises(Request $request)
    {
        return $this->getEntitiesReq($request);
    }

    /**
     * Update multiple advertises
     */
    public function putAdvertises(Request $request)
    {
        return response('Posts batch editing API unimplemented', 401);
    }

    /**
     * Move multiple advertises into trash
     */
    public function deleteAdvertises(Request $request)
    {
        return response('API unimplemented', 401);
    }

    /**
     * Return advertise states and occurrences
     */
    public function getStates(Request $request)
    {
        return $this->getEntityStates($request, 'advertises');
    }

    /**
     * Get a advertise with it's relations
     * @param Request $request
     * @param $id - advertise id
     * @return string
     */
    public function getAdvertise(Request $request, $id)
    {
        return $this->getEntityReq($request, 'id', $id, null);
    }

    /**
     * Update advertise by given id
     * @param Request $request
     * @param $id - advertise id to be updated
     * @return object
     */
    public function putAdvertise(Request $request, $id)
    {
        return $this->putEntityReq($request, 'id', $id);
    }

    /**
     * Create a new advertise
     * @param Request $request
     * @return object
     */
    public function postAdvertise(Request $request)
    {
        return $this->postEntityReq($request);
    }

    /**
     * Move a advertise to trash by id
     * @param Request $request
     * @param $id
     * @return Advertise
     */
    public function deleteAdvertise(Request $request, $id)
    {
        return $this->deleteEntityReq($request, 'id', $id);
    }
}
