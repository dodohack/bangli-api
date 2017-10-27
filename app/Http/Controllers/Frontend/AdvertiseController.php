<?php
/**
 * Affiliates offer controller
 */

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;

class AdvertiseController extends FeController
{

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }
    /**
     * Return a list of published advertisements
     * @param Request $request
     * @return object
     */
    public function getAds(Request $request)
    {
        $ads = $this->getEntitiesByKey($request->all(), null, null, null, 'none');

        return $this->response($ads, 'get ads error');
    }

    /**
     * Get an advertisement
     * @param Request $request
     * @param $id
     * @return object
     */
    public function getAd(Request $request, $id)
    {
        $ad = $this->getEntity('id', $id);

        return $this->response($ad, 'get ad error');
    }
}