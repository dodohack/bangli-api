<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Concerns\RoutesRequests;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    // The domain this API server serves, one of following configed in .env
    // bangli.uk, huluwa.uk, bangli.us, ...
    // www.bangli.uk, www.huluwa.uk, www.bangli.us, ...
    protected $domain;
    protected $www;
    // Image server address with S3 bracket name
    protected $imgServer;

    public function __construct()
    {
        $this->domain = env('ROOT_DOMAIN');
        $this->www = 'www.' . $this->domain;
        $this->imgServer = env('IMG_SERVER');
    }

    /**
     * Return JSONP or AJAX response based on client request
     * I intentionally place $etype in the middle even it has default parameter
     * @param string $callback
     * @param string $etype - entity etype if returned data are/is entities/entity
     * @param $data - data array to be returned to client
     * @return
     */
    public function success($callback, $etype = null, $data)
    {
        // Convert scalar to array
        if (!is_array($data)) $data = compact('data');

        if ($etype) $data['etype'] = $etype;

        $ret = json_encode($data);

        // JSONP response
        if ($callback)
            return $callback . '(' . $ret . ')';

        // AJAX response
        return $ret;
    }

    public function successReq(Request $request, $data)
    {
        $callback = $request->get('callback');
        $etype    = $request->get('etype');
        return $this->success($callback, $etype, $data);
    }

    /**
     * Return error message with HTTP 500 error
     * @param string $callback
     * @param string $etype
     * @param string $msg
     * @return
     */
    public function error($callback, $etype = null, $msg)
    {
        $ret = ['error' => $msg];
        if ($etype) $ret['etype'] = $etype;

        $ret = json_encode($ret);

        // Jsonp
        if ($callback) $ret = $callback . '(' . $ret . ')';

        return response($ret, 500);
    }

    public function errorReq(Request $request, $msg)
    {
        $callback = $request->get('callback');
        $etype    = $request->get('etype');
        return $this->error($callback, $etype, $msg);
    }

    /**
     * Response to client with data or error
     * @param Request $request
     * @param $data
     * @param $error
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory|string
     */
    public function responseReq(Request $request, $data, $error)
    {
        $callback = $request->get('callback');
        $etype    = $request->get('etype');
        if ($data)
            return $this->success($callback, $etype, $data);
        else
            return $this->error($callback, $etype, $error);
    }

    /**
     * Sanitize callback for input, strip all specially characters
     * @param $value
     */
    public function sanitize(&$value)
    {
        $value = filter_var($value, FILTER_SANITIZE_STRING);
    }

    /**
     * Decode parameter string into MySQL query parameter key-value array
     * @param $paramStr - http request parameter string: [key:value;]*
     * @return array - key-value parameter array
     */
    public function decodeParams($paramStr)
    {
        $params = [];
        $pairs = explode(';', $paramStr);

        foreach ($pairs as $pair) {
            $kv = explode(':', $pair);
            $params[$kv[0]] = $kv[1];
        }

        return $params;
    }
}
