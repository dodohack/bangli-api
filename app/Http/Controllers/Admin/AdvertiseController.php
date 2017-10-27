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
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Return a list of advertises
     * @param Request $request
     * @return object
     */
    public function getAdvertises(Request $request)
    {
        $ads = $this->getEntities($request->all());

        return $this->response($ads, 'get ads error');
    }

    /**
     * Update multiple advertises
     */
    public function putAdvertises(Request $request)
    {
        return $this->error('API unimplemented');
    }

    /**
     * Move multiple advertises into trash
     */
    public function deleteAdvertises(Request $request)
    {
        $ids = $request->get('ids');
        $numDeleted = $this->deleteEntities($ids);

        return $this->response($numDeleted, 'trash ads error');
    }

    /**
     * Physically delete advertises from trash
     * @param Request $request
     * @return
     */
    public function purgeAdvertises(Request $request)
    {
        $ids = $request->get('ids');
        $numPurged = $this->purgeEntities($ids);

        return $this->response($numPurged, 'purge ads error');
    }

    /**
     * Return advertise status and occurrences
     */
    public function getStatus(Request $request)
    {
        $status = Advertise::select(DB::raw('status, COUNT(*) as count'))
            ->groupBy('status')->get();

        return $this->response($status, 'get ad status error');
    }

    /**
     * Get a advertise with it's relations
     * @param Request $request
     * @param $id - advertise id
     * @return string
     */
    public function getAdvertise(Request $request, $id)
    {
        $ad = $this->getEntity('id', $id, null);

        return $this->response($ad, 'get ad error');
    }

    /**
     * Update advertise by given id
     * @param Request $request
     * @param $id - advertise id to be updated
     * @return object
     */
    public function putAdvertise(Request $request, $id)
    {
        $ad = $this->putEntity($request->all(), 'id', $id);

        return $this->response($ad, 'put ad error');
    }

    /**
     * Create a new advertise
     * @param Request $request
     * @return object
     */
    public function postAdvertise(Request $request)
    {
        $ad = $this->postEntity($request->all());

        return $this->response($ad, 'post ad error');
    }

    /**
     * Move a advertise to trash by id
     * @param Request $request
     * @param $id
     * @return Advertise | bool
     */
    public function deleteAdvertise(Request $request, $id)
    {
        $deleted = $this->deleteEntity('id', $id);

        return $this->response($deleted, 'trash ad error');
    }

    /**
     * Physically delete a advertise from trash
     * @param Request $request
     * @param $id
     * @return
     */
    public function purgeAdvertise(Request $request, $id)
    {
        $purged = $this->purgeEntity('id', $id);

        return $this->response($purged, 'purge ad error');
    }
}
