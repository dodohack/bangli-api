<?php
/**
 * Dashboard geo-location management controller
 */

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Http\Request;

use App\Http\Controllers\EntityController;
use App\Models\Location;
use League\Flysystem\Adapter\Local;

class LocationController extends EntityController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Return a list of locations
     * @param Request $request
     * @return object
     */
    public function getLocations(Request $request)
    {
        $ret = Location::all()->toArray();

        return $this->response($ret, 'get geo locations error');
    }

    /**
     * Update multiple locations
     */
    public function putLocations(Request $request)
    {
        return $this->error('API unimplemented');
    }

    /**
     * Move multiple locations into trash
     */
    public function deleteLocations(Request $request)
    {
        return $this->error('API unimplemented');
    }

    /**
     * Get a location with it's relations
     * @param Request $request
     * @param $id - location id
     * @return string
     */
    public function getLocation(Request $request, $id)
    {
        $ret = Location::find($id)->toArray();

        return $this->response($ret, 'get geo location error');
    }

    /**
     * Update location by given id
     * @param Request $request
     * @param $id - location id to be updated
     * @return object
     */
    public function putLocation(Request $request, $id)
    {
        $inputs = $request->except('id');
        $loc = Location::find($id);
        $loc->update($inputs);

        return $this->response($loc, 'put loc fail');
    }

    /**
     * Create a new location
     * @param Request $request
     * @return object
     */
    public function postLocation(Request $request)
    {
        $inputs = $request->except('id');

        $newLoc = Location::create($inputs)->toArray();

        return $this->response($newLoc, 'post geo location fail');
    }

    /**
     * Move a location to trash by id
     * @param Request $request
     * @param $id
     * @return
     */
    public function deleteLocation(Request $request, $id)
    {
        if (Location::destroy($id))
            return $this->success(['id' => $id]);

        return $this->error('delete geo location error');
    }
}
