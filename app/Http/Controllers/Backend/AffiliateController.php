<?php

/**
 * Base class of affiliate client
 */

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\EntityController;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Client;

use App\Models\OfferFilter;

class AffiliateController extends EntityController
{
    private $contentFilters;
    private $merchantIds = array();
    private $contentExtenders = array();

    public function __construct()
    {
        $record = OfferFilter::where('type', 'CONTENT')->first(['content']);
        $this->contentFilters = explode(PHP_EOL, $record['content']);

        $record = OfferFilter::where('type', 'MID')->first(['content']);
        $merchants = explode(PHP_EOL, $record['content']);
        foreach($merchants as $m)
            array_push($this->merchantIds, explode('|', $m));

        $record = OfferFilter::where('type', 'EXTEND')->first(['content']);
        $phases = explode(PHP_EOL, $record['content']);
        foreach($phases as $p)
            array_push($this->contentExtenders, explode('|', $p));
    }

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

        // Read up to 20M data from returned stream
        return $res->getBody()->read(1024*1024*20);
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


    /**
     * Test if the offer matches any filters we have set in the content filters,
     * if it matches, then we say the offer is not a good one.
     * @params text - offer title, description or content
     * @return bool - true if the offer passes the test
     */
    protected function contentFilter($text)
    {
        if ($text == '') return false;

        foreach($this->contentFilters as $reg) {
            // Skip empty lines
            if ($reg == '') continue;
            // Matches, return not pass
            if (preg_match('/'.$reg.'/', $text)) return false;
        }

        return true;
    }

    /**
     * Test if the offer belongs to the merchant we don't want to collect
     * their offer, this is mainly because they have out-dated or unmaintained
     * feeds.
     * @params platform - affiliate platform, 'AWIN', 'LINKSHARE' etc
     * @params mid  - merchant ID on given platform
     * @return bool - true if the offer passes the test
     */
    protected function merchantIdFilter($platform, $mid)
    {
        if ($platform == '' || $mid == '') return false;

        foreach($this->merchantIds as $merchants) {
            // Matches, return not pass
            if ($platform == $merchants[0] && $mid == $merchants[1]) return false;
        }

        return true;
    }

    /**
     * Test if the start and end date is within the requirement range.
     * @params date - date to be tested
     * @params isStart - if the date is a start date or end date
     * @return bool - true if the offer passes the test
     */
    protected function dateFilter($date, $isStart)
    {
        // FIXME: Some merchants do have some long standing offers.
        return true;
    }

    /**
     * @param $text - offer title
     * @return string - return extended or unmodified offer title
     */
    protected function contentExtender($text)
    {
        return $text;
    }
}