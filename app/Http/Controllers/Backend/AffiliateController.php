<?php

/**
 * Base class of affiliate client
 */

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\EntityController;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Client;

class AffiliateController extends EntityController
{
    public function updateMerchants()
    {
        assert("Child class must implement");
    }

    public function updateOffers()
    {
        assert("Child class must implement");
    }

    protected function retrieveData($api)
    {
        $client = new Client();

        try {
            $res = $client->request('GET', $api);
        } catch (ServerException $e) {
            // TODO: handle network exception
            return false;
        }

        // Read up to 10M data from returned stream
        return $res->getBody()->read(1024*1024*10);
    }

    /**
     * Convert a string into url friendly name
     * @param $name
     * @return mixed|string
     */
    protected function urlfy($name)
    {
        // Decode html sepcial chars, say &amp;
        $res = htmlspecialchars_decode($name);

        // Remove tailing characters such UK
        $res = preg_replace('/UK/', '', $res);

        // Remove specially characters and lowercase the string
        $res = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $res));

        // Remove multiple dash to single dash, say '--', '---' to '-'.
        $res = preg_replace('/\-[\-]+/', '-', $res);

        // Remove tailing '-'
        if (substr($res, -1) == '-') $res = substr($res, 0, -1);

        return $res;
    }
}