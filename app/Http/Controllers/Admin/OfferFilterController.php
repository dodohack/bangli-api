<?php
/**
 * Offer filter setting controller
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\OfferFilter;

class OfferFilterController extends Controller
{

    public function getAll(Request $request)
    {
        $ret = OfferFilter::get()->toJson();
        return parent::success($request, $ret);
    }

    public function get(Request $request, $type)
    {
        $ret = OfferFilter::where('type', $type)->get()->toJson();
        return parent::success($request, $ret);
    }

    public function put(Request $request, $type)
    {
        $content = $request->get('content');
        $ret = OfferFilter::where('type', $type)->update(['content' => $content]);
        if ($ret) {
            return $this->get($request, $type);
        } else {
            return response('FAIL', 401);
        }
    }

    public function post(Request $request)
    {
        $input = $request->except('id');
        $ret = OfferFilter::create($input);
        if ($ret) {
            return parent::success($request, json_encode($ret));
        } else {
            return resopnse('FAIL', 401);
        }
    }
}
