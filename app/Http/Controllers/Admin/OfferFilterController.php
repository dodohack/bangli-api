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
        $ret = OfferFilter::get();
        return parent::responseReq($request, $ret, 'get all offer filter error');
    }

    public function get(Request $request, $type)
    {
        $ret = OfferFilter::where('type', $type)->first();
        return parent::responseReq($request, $ret, 'get offer filter error');
    }

    public function put(Request $request, $type)
    {
        $content = $request->get('content');
        $ret = OfferFilter::where('type', $type)->update(['content' => $content]);
        return parent::responseReq($request, $ret, 'put offer filter error');
    }

    public function post(Request $request)
    {
        $input = $request->except('id');
        $ret = OfferFilter::create($input);
        return parent::responseReq($request, $ret, 'post offer filter error');
    }
}
