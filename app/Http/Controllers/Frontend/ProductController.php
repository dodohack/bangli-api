<?php
/**
 * Affiliated products controller
 */

namespace App\Http\Controllers\Frontend;

use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ProductController extends FeController
{
    // ElasticSearch affiliated product endpoint
    protected $es;
    protected $cdn;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->es = env('ES_ENDPOINT') . '/' . env('ES_PRODUCT');
        $this->cdn = env('PRODUCT_IMG_SERVER');
    }

    /**

    _msearch example:

    curl -XPOST http://localhost:9200/products-v2/_msearch?pretty -d '
    {}
    {"query" : { "bool": { "must": [ {"match" : { "name": { "query" : "Mediheal mask", "operator" : "and" } } } ,{"term" : { "domain" : "baobella" } } ] } }, "size" : 1}
    {}
    {"query" : { "bool": { "must": [ {"match" : { "name" : { "query" : "A.H.C mask", "operator" : "and" } } } ,{"term" : { "domain" : "baobella" } } ] } }, "size" : 1}
    {}
    {"query" : { "bool": { "must": [ {"match" : { "name" : { "query" : "JAYJUN mask", "operator" : "and" } } } ,{"term" : { "domain" : "baobella" } } ] } }, "size" : 1}
    '
     */

    /**
     * We use _msearch instead of _search here
     * @param Request $request
     */
    public function get(Request $request)
    {
        $id       = $request->get('id');   // html tag id, requried
        $name     = $request->get('name'); // Optional, Product name,
        $brand    = $request->get('brand'); // Optional
        $domain   = $request->get('domain'); // Optional
        $count    = $request->get('count');  // Number of cards
        $order    = $request->get('order'); // Optional, sorting

        $client = new Client();
        $search_api = $this->es . '/_msearch';

        if (!$id || !$count)
            return $this->error("Product card id or count is missing");

        if (!($name || $brand))
            return $this->error("Missing product name or brand");

        $body = $this->getMSearchQueryBody($name, $brand, $domain, $count, $order);

        try {
            $res = $client->request('POST', $search_api, ['body' => $body]);
        } catch (ServerException $e) {
            return $this->error('Search engine exception');
        }

        // Decode to json
        $res = json_decode($res->getBody()->read(1024*1024));

        // We got an array of object in "responses": [{}, {}] for _msearch
        $length = count($res->responses);

        $products = [];

        for ($i = 0; $i < $length; $i++) {
            $product = $res->responses[$i]->hits->hits->_source;
            $product->url = $this->buildTrackingUrl($product->url,
                'product-card', null, $product->domain);

            if ($product->images) {
                $product->thumbs = $this->cdn . 'thumbs/medium/' .
                    basename($product->images[0]->path);

                $product->images = $this->cdn . 'thumbs/big/' .
                    basename($product->images[0]->path);
            } else {
                $product->thumbs = '';
                $product->images = '';
            }

            $products[] = $product;
        }

        if ($length) {
            $results = ['id' => $id, 'products' => $products, 'total' => $length];
            return $this->success($results);
        } else {
            return $this->error('No result found');
        }

    }

    private function getMSearchQueryBody($name, $brand, $domain, $count, $order)
    {
        $length_name   = 0;
        $length_brand  = 0;
        $length_domain = 0;
        // We use $count as the max number of products
        if ($name) {
            $name     = explode('|', $name);
            $length_name = count($name);
        }
        if ($brand) {
            $brand   = explode('|', $brand);
            $length_brand = count($brand);
        }
        if ($domain) {
            $domain = explode('|', $domain);
            $length_domain = count($domain);
        }

        $body = '';
        for ($i = 0; $i < $count; $i++) {
            $query_name = '';
            if ($length_name) {
                $idx = max($i, $length_name - 1);
                $query_name = '{"match" : { "name": { "query" : "'. $name[$idx] .'", "operator" : "and" } } }';
            }

            $query_brand = '';
            if ($length_brand) {
                $idx = max($i, $length_brand - 1);
                $query_brand = '{"term": {"brand": "'. $brand[$idx] .'"} }';
            }

            $query_domain = '';
            if ($length_domain) {
                $idx = max($i, $length_domain - 1);
                $query_domain= '{"term": {"domain": "'. $domain[$idx] .'"} }';
            }

            $query = '';
            if (($query_name != '' && $query_brand != '') ||
                ($query_name != '' && $query_domain != '') ||
                ($query_brand != '' && $query_domain != '')) {

                $query_inner = '';
                if ($query_name != '')
                    $query_inner = $query_name;

                if ($query_brand != '') {
                    if ($query_inner == '') $query_inner = $query_brand;
                    else $query_inner = $query_inner . ', ' . $query_brand;
                }

                if ($query_domain != '') {
                    if ($query_inner == '') $query_inner = $query_domain;
                    else $query_inner = $query_inner . ', ' . $query_domain;
                }

                $query = '{"query": {"bool": { "must": [' . $query_inner . '] } }, "size": 1 }';

            }

            // Append to body
            $body .= '{}' . PHP_EOL . $query . PHP_EOL;
        }

        return $body;
    }
}

