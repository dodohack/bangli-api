<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * Return JSONP or AJAX response based on client request
     * @param Request $request
     * @param $json
     * @return string
     */
    public function success(Request $request, $json)
    {
        // JSONP response
        if ($request->has('callback'))
            return $request->input('callback') . '(' . $json . ')';

        // AJAX response
        return $json;
    }

    /**
     * Same as function 'success', but with array input
     * @param $inputs
     * @param $json
     * @return string
     */
    public function successV2($inputs, $json)
    {
        // JSONP response
        if (isset($input['callback']))
            return $inputs['callback'] . '(' . $json . ')';

        // AJAX response
        return $json;
    }
}
