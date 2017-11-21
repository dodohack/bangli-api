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

    // _msearch or _search mode
    protected $mode;

    // product card arguments
    protected $id;
    protected $name;
    protected $brand;
    protected $domain;
    protected $count;
    protected $order;

    // How many name, brand, domain in their array
    protected $length_name;
    protected $length_brand;
    protected $length_domain;


    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->es = env('ES_ENDPOINT') . '/' . env('ES_PRODUCT');
        $this->cdn = env('PRODUCT_IMG_SERVER');
        $this->mode          = 's'; // Single search
        $this->length_name   = 0;
        $this->length_brand  = 0;
        $this->length_domain = 0;
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
     * Type of search we support:
     * We can support unaligned number of name, brand, domain as previous  version.
     * [products name="..."]
     * [products name="..." domain="..."]
     * [products name="..." brand="..." domain="..."]
     * [products name="..." domain="...|...|..."]
     * [products name="..." brand="..." domain="...|...|..."]
     * [products name="..." brand="...|...|..." domain="...|...|..."]
     * [products name="...|...|..." domain="...|...|..."]
     * [products name="...|...|..." brand="...|...|..." domain="...|...|..."]
     *
     * [products brand="..." count=""]
     *
     * [products domain="..." count=""]
     * [products domain="..." count="" order=""]
     * [products domain="..." name="...|...|..."]
     * [products domain="..." name="...|...|..." brand="..."]
     * [products domain="..." name="...|...|..." brand="...|...|..."]
     * [products domain="..." brand="..." count=""]
     * [products domain="..." brand="..." count="" order=""]
     * [products domain="..." brand="...|...|..." count=""]
     * [products domain="..." brand="...|...|..." count="" order=""]
     *
     * When query product by name, we use _msearch api.
     * When query product by brand or domain, we use _search api with aggragate
     * so that we can sort the result by 'rating' or 'discount rate'.
     * @param Request $request
     */
    public function get(Request $request)
    {
        if (!$this->initialize($request))
            return $this->error("Product card parameter error");

        $client = new Client();

        if ($this->mode == 'm') {
            // Multiple name and single/multi domain[s]
            $search_api = $this->es . '/_msearch';
            $body = $this->getMultiSearchQueryBody();
        } else {
            // All other case:

            // 1. No name is given, domain should be given, brand is optionally given
            // We sort products from given domain by given order(rating default)

            // 2. Single name from single domain or multiple domains
            // Get 1 product from single domain if count = 1
            // Get n products from single domain if count > 1
            // Get 1 product from each domain if count = 1 and domain > 1
            $search_api = $this->es . '/_search';
            $body = $this->getSingleSearchQueryBody();
            $body = $this->getAggregateQueryBody($body);
        }

	//dd($body);

        try {
            $res = $client->request('POST', $search_api, ['body' => $body]);
        } catch (ServerException $e) {
            return $this->error('Search engine exception');
        }

        // Decode to json
        $res = json_decode($res->getBody()->read(1024*1024));

        $products = [];
        $length   = 0;

        if ($this->mode == 'm') {
            // We got an array of object in "responses": [{}, {}] from _msearch
            $response_length = count($res->responses);
	    //dd($res->responses);
            for ($i = 0; $i < $response_length; $i++) {
                $hits = $res->responses[$i]->hits;
                if ($hits->total) {
		    foreach($hits->hits as $hit) {
			$product = $hit->_source;
			$this->updateProduct($product);
			$products[] = $product;
			$length++;
		    }
                }
            }
        } else {
            // Get result as aggregate from _search
            if ($res->hits->total) {
                $buckets = $res->aggregations->by_domain->buckets;
                $bucket_count = count($buckets);
                // Loop over buckets(by_domain)
                for ($i = 0; $i < $bucket_count; $i++) {

                    $docs = $buckets[$i]->tops->hits->hits;
                    $size = min($this->count, $buckets[$i]->tops->hits->total);

                    // Loop over documents per domain
                    for ($j = 0; $j < $size; $j++) {
                        $product = $docs[$j]->_source;
                        $this->updateProduct($product);
                        $products[] = $product;
                        $length++;
                    }
                }
            }
        }

        if ($length) {
            $results = [
                'id' => $this->id,
                'products' => $products,
                'total' => $length
            ];
            return $this->success($results);
        } else {
            return $this->error('No result found');
        }

    }

    /**
     * In this case, we have more than one names or more than one brands
     * @return string
     */
    private function getMultiSearchQueryBody()
    {
        assert($this->length_domain && "Domain should be given");

        $body = '';
        $max_name_idx   = $this->length_name - 1;
        $max_brand_idx  = $this->length_brand - 1;
        $max_domain_idx = $this->length_domain - 1;

        $sort = $this->getSortString();
        $max = max($this->length_name, $this->length_brand);

        // Use the max number of name or brand as max step
        $steps = $max;
	$size  = $this->count;

	if ($this->length_domain == 1 && $max > 1 && $this->count > $max) {
	    $size  = $this->count / $max;
	}

	$outer = $this->length_domain;
	$inner = $steps;
	
	if ($steps == $size && $steps == 1) {
	    $outer = 1;
	    $inner = $this->length_domain;
	}

	$inner_starts = 0;
        for($i = 0; $i < $outer; $i++) {

	    // If we have same count of name/brand and domain
	    if ($outer == $inner)
		$inner_starts = $i;
	    
	    for ($j = $inner_starts; $j < $inner; $j++) {
		$query_name = '';
		if ($this->length_name) {
                    $idx = $j > $max_name_idx ? $max_name_idx : $j;
                    $query_name = '{"match" : { "name": { "query" : "' . $this->name[$idx] . '", "operator" : "and" } } }';
		}

		$query_brand = '';
		if ($this->length_brand) {
                    $idx = $j > $max_brand_idx ? $max_brand_idx : $j;
                    $query_brand = '{"term": {"brand": "' . $this->brand[$idx] . '"} }';
		}
		
		$idx = $j > $max_domain_idx ? $max_domain_idx : $j;
		$query_domain = '{"term": {"domain": "' . $this->domain[$idx] . '"} }';
		
		$tmp = [];
		if ($query_name)
                    $tmp [] = $query_name;
		if ($query_brand)
                    $tmp [] = $query_brand;
		$tmp [] = $query_domain;
		$query_inner = implode(',', $tmp);
		
		$query = '{"query": {"bool": { "must": [' . $query_inner . '] } }, '. $sort .' "size": ' . $size . '}';
		$body .= '{}' . PHP_EOL . $query . PHP_EOL;

		// Only iterate once if name/brand count equals domain
		if ($outer == $inner)
		    break;
	    }
        }

        return $body;
    }

    /**
     * In this case, name is single item or not given
     */
    private function getSingleSearchQueryBody()
    {
        $query_name  = '';
        $query_brand = '';
        $query_domain = '';

        if ($this->length_name == 1) {
            $name = $this->name[0];
            $query_name = '{
                "match": {
                    "name": {
                        "operator": "and",
                        "query": "' . $name . '"
                    }
                }
            }';
        }

        // Brand can be only 1 string within this case, otherwise it is
        // processed by _msearch
        if ($this->length_brand == 1) {
            $query_brand = '{"term": {"brand": "' . $this->brand[0] .'"} }';
        }

        if ($this->length_domain) {
            if ($this->length_domain == 1) {
                // Get 1 product or 1 series of products from single domain
                $query_domain = '{ "term": { "domain": "' . $this->domain[0] . '" } }';
            } else {
                // Get price comparison of 1 product from multiple domains
                $tmp = [];
                foreach ($this->domain as $d)
                    $tmp [] = '{ "term": { "domain": "' . $d . '"} }';
                $query_domain = '{
                        "bool": {
                            "should": [ '. join(',', $tmp) .' ]
                        }
                    }';
            }
        }

        $tmp = [];
        if ($query_name)
            $tmp [] = $query_name;
        if ($query_brand)
            $tmp [] = $query_brand;
        if ($query_domain)
            $tmp [] = $query_domain;

        $conditions = count($tmp);
        assert($conditions > 0 && "At least 1 query should be given");

        if ($conditions > 1) {
            $query = implode(',', $tmp);
            return '{
                    "bool": {
                        "must": [
                             '. $query . '
                        ]
                    }
                }';
        } else {
            $query = $tmp[0];
            return $query;
        }
    }

    /**
     * Construct aggregate query with given query body
     * @param $body
     * @return mixed
     */
    private function getAggregateQueryBody($body)
    {
        $sort = $this->getSortString();

        $new_body = '{
            "query": ' . $body . ',
            "size": 0,
            "aggs": {
                "by_domain": {
                    "terms": { 
                        "field": "domain" 
                    },
                    "aggs": {
                        "tops": {
                            "top_hits": {
                                "size": '. $this->count .',
                                ' . $sort . '
                                "_source": ["domain", "url", "brand", "categories", "name",
                                            "price", "RRP", "discount", "offer_info", "spec",
                                            "unit", "images", "rating", "review_count"]
                            }
                        }
                    }
                }
            }

        }';
        return $new_body;
    }

    private function initialize(Request $request)
    {
        $this->id       = $request->get('id');   // html tag id, required
        $this->name     = $request->get('name'); // Optional, Product name,
        $this->brand    = $request->get('brand'); // Optional
        $this->domain   = $request->get('domain'); // Optional
        $this->count    = $request->get('count', 1);  // Number of cards
        $this->order    = $request->get('order'); // Optional, sorting

        // We use $count as the max number of products
        if ($this->name) {
            $this->name     = explode('|', $this->name);
            $this->length_name = count($this->name);
        }

        if ($this->brand) {
            $this->brand   = explode('|', $this->brand);
            $this->length_brand = count($this->brand);
        }

        if ($this->domain) {
            $this->domain = explode('|', $this->domain);
            $this->length_domain = count($this->domain);
        }

        // We must have id and one of name/brand/domain
        if (!$this->id && !($this->name || $this->brand || $this->domain))
            return false;
        // When name is not given, domain must be specified
        if (!$this->name && !$this->domain)
            return false;

        // Domain must be specified if name is more than 1
        if ($this->length_name > 1 && $this->length_domain == 0)
            return false;

        // Domain must be specified if brand is more than 1
        if ($this->length_brand > 1 && $this->length_domain == 0)
            return false;

        // _msearch mode
        if ($this->length_name > 1 || $this->length_brand > 1)
            $this->mode = 'm';

        return true;
    }

    private function updateProduct(&$product)
    {
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
    }

    private function getSortString()
    {
        $sort = '';
        if ($this->order == 'rating')
            $sort = '"sort": [{"rating": {"order": "desc"}}],';
        if ($this->order == 'discount')
            $sort = '"sort": [{"discount": {"order": "asc"}}],';
        return $sort;
    }

}

