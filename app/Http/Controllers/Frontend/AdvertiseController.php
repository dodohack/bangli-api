<?php
/**
 * Affiliates offer controller
 */

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;

class AdvertiseController extends FeController
{

    /**
     * Return a list of published advertisements
     * @param Request $request
     */
    public function getAds(Request $request)
    {
        $result = $this->getArrayEntitiesByKey($request->all(), null, null, null, 'none');
        // FIXME: Error handling.
        return $this->success($request, json_encode($result));
    }

    /**
     * Get an advertisement
     * @param Request $request
     * @param $id
     */
    public function getAd(Request $request, $id)
    {

    }
}