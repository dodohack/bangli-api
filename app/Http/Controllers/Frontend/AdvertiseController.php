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
        $ret = $this->getArrayEntitiesByKey($request->all(), null, null, null, 'none');
        // FIXME: Error handling.
        return parent::successReq($request, $ret);
    }

    /**
     * Get an advertisement
     * @param Request $request
     * @param $id
     * @return
     */
    public function getAd(Request $request, $id)
    {
        $ret = $this->getEntityReq($request, 'id', $id);
        return parent::responseReq($request, $ret, 'get ad error');
    }
}