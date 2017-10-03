<?php
/**
 * Ping controller, it actually does nothing, returns user specified key only
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class PingController extends Controller
{
    /**
     * This function handles ping service, it just return the key sent from
     * client, or error if key is not specified
     */
    public function handle(Request $request)
    {
        $key = $request->input('key', null);
        if (!$key) return response('Incorrect parameter', 400);
        return parent::success($request, json_encode($key));
    }
}
