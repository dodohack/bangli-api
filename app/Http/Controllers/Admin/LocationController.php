<?php
/**
 * Dashboard geo-location management controller
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Controllers\EntityController;
use App\Models\Location;

class LocationController extends EntityController
{
    /**
     * Return a list of locations
     * @param Request $request
     * @return object
     */
    public function getLocations(Request $request)
    {
        return $this->getEntitiesReq($request);
    }

    /**
     * Update multiple locations
     */
    public function putLocations(Request $request)
    {
        return response('Posts batch editing API unimplemented', 401);
    }

    /**
     * Move multiple locations into trash
     */
    public function deleteLocations(Request $request)
    {
        return response('API unimplemented', 401);
    }

    /**
     * Return location statuss and occurrences
     */
    public function getStates(Request $request)
    {
        return $this->getEntityStates($request, 'locations');
    }

    /**
     * Get a location with it's relations
     * @param Request $request
     * @param $id - location id
     * @return string
     */
    public function getLocation(Request $request, $id)
    {
        return $this->getEntityReq($request, 'id', $id, null);
    }

    /**
     * Update location by given id
     * @param Request $request
     * @param $id - location id to be updated
     * @return object
     */
    public function putLocation(Request $request, $id)
    {
        return $this->putEntityReq($request, 'id', $id);
    }

    /**
     * Create a new location
     * @param Request $request
     * @return object
     */
    public function postLocation(Request $request)
    {
        return $this->postEntityReq($request);
    }

    /**
     * Move a location to trash by id
     * @param Request $request
     * @param $id
     * @return Location
     */
    public function deleteLocation(Request $request, $id)
    {
        return $this->deleteEntityReq($request, 'id', $id);
    }
}
