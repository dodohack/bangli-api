<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

    // Jsonp callback from incoming request, it is injected by the
    // constructor from child class
    protected $callback;

    // Entity type  - Only entity related controller will initialize it
    protected $etype;

    public function __construct(Request $request)
    {
        $this->domain    = env('ROOT_DOMAIN');
        $this->www       = 'www.' . $this->domain;
        $this->imgServer = env('IMG_SERVER');
        $this->callback  = $request->get('callback');
        $this->etype     = null;
    }

    /**
     * Return JSONP or AJAX response based on client request
     * @param $data - key-value array of returned data
     * @return string - json string like '{etype: ...}, data'
     */
    public function success(array $data)
    {
        // Must be array
        assert(is_array($data), "Array expected");

        if ($this->etype) $data['etype'] = $this->etype;

        $ret = json_encode($data);

        // JSONP response
        if ($this->callback)
            return $this->callback . '(' . $ret . ')';

        // AJAX response
        return $ret;
    }

    /**
     * Return error message with HTTP 500 error
     * @param string $callback
     * @param string $msg
     * @return
     */
    public function error(string $msg)
    {
        // Must be scalar or string.
        assert(is_string($msg), "String expected");

        $ret = ['error' => $msg];
        if ($this->etype) $ret['etype'] = $this->etype;

        $ret = json_encode($ret);

        // Jsonp response
        if ($this->callback) $ret = $this->callback . '(' . $ret . ')';

        return response($ret, 500);
    }

    /**
     * Response to client with data or error
     * @param Request $request
     * @param $data
     * @param $error
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory|string
     */
    public function response(array $data, string $error)
    {
        if (is_array($data))
            return $this->success($data);
        else
            return $this->error($error);
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
