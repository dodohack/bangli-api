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
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function getAll(Request $request)
    {
        $ret = OfferFilter::get()->toArray();
        return $this->response($ret, 'get all offer filter error');
    }

    public function get(Request $request, $type)
    {
        $ret = OfferFilter::where('type', $type)->first();
        return $this->response($ret, 'get offer filter error');
    }

    public function put(Request $request, $type)
    {
        $content = $request->get('content');
        $record = OfferFilter::where('type', $type);
        $record->update(['content' => $content]);
        $ret = $record->get()->toArray();
        return $this->response($ret, 'put offer filter error');
    }

    public function post(Request $request)
    {
        $input = $request->except('id');
        $ret = OfferFilter::create($input);
        return $this->response($ret, 'post offer filter error');
    }
}
