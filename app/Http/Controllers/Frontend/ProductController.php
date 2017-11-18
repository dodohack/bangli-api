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
     Group result by domain,
     ref1: https://stackoverflow.com/questions/25986538/elasticsearch-filter-document-group-by-field
     ref2: https://m.alphasights.com/practical-guide-to-grouping-results-with-elasticsearch-a7e544343d53
     Example:

    curl -XGET 'http://localhost:9200/products_test/_search?pretty' -d '
    {
      "query": {
         [All the query staff goes here]
      },
      "size": 0,
      "aggs": {
         "by_domain": {
            "terms": { "field": "domain" },
            "aggs": {
               "tops": {
                  "top_hits": { "size": 2 }
                }
            }
         }
      }
    }'
    */

    /**
     Modes:

     # 1. Get multiple products from single website
     <products
     mode="multiple"
     name="liz earle skin tonic|no 7 smooth gentle|simple kind to skin soothing|la mer"
     domain="boots"/>

    # 2. Get multiple products from different website
    <products
     mode="multiple"
     name="liz earle skin tonic|no 7 smooth gentle|simple kind to skin soothing"
     domain="lookfantastic|boots|allbeauty"/>

    <products
    mode="multiple"
    name="liz earle skin tonic|no 7 smooth gentle|simple kind to skin soothing"
    brand="rodial|nip fab|holland barrett"/>

    # 3. Compare single product cross different websites
    <products
     mode="compare"
     name="skin tonic"
     brand="liz earle"/>

    # 4. Compare single product cross websites as specified
    <products
    mode="compare"
    name="skin tonic" brand="liz earle"
    domain="boots|lookfantastic|allbeauty|harrods"/>

    # 5. Optional: Compare similar product cross different brand cross different websites
    <products
    mode="compare"
    name="face cream" brand="liz earle|the body shop|la mer|..."/>

    # 6. Get single product from single website
    <products
     mode="single"
     name="liz earle skin tonic"
     domain="boots"/>
     */

    /*
     * Return a list of published advertisements
     * Request example:
     * <endpoint>/products?
     *  <mode=multiple|compare|single>
     *  <name=product name string, multiple name separated by '|'>
     *  [brand=brand name string]
     *  [category=category string]
     *  [domain=website root domain]
     *  [rating=0-5 floating point, default 0]
     *  [review_count=the minimal review count of the products, default 0]
     *  [discount=0-1 floating point, only get products whose discount
     *     is better then this(the lower of the number, the better), default 1]
     *  [size=number of products]
     *  [mode=<lowest price products|related products on same website>]
     *
     * @param Request $request
     * @return object
     */
    public function get(Request $request)
    {
        $mode     = $request->get('mode'); // Request mode
        $name     = $request->get('name'); // Product name, required
        $brand    = $request->get('brand'); // Optional
        $category = $request->get('category'); // Optional
        $domain   = $request->get('domain'); // Optional

        $client = new Client();
        $search_api = $this->es . '/_search';

        if (!$name || !$mode)
            return $this->error("Product card mode or product name are missing");

        $query = $this->getProductsQueryBody($name, $brand, $domain, $category);

        // Request body with aggregate by domain with top 1 result of each domain
        $body = '
        {
            "query": ' . $query . ',
            "size": 0,
            "aggs": {
                "by_domain": {
                    "terms": { 
                        "field": "domain" 
                    },
                    "aggs": {
                        "tops": {
                            "top_hits": { 
                                "size": 1,
                                "_source": ["domain", "url", "brand", "categories", "name",
                                            "price", "RRP", "discount", "offer_info", "spec",
                                            "unit", "images", "rating", "review_count"]
                            }
                        }
                    }
                }
            }
        }';


        try {
            $res = $client->request('POST', $search_api, ['body' => $body]);
        } catch (ServerException $e) {
            return $this->error('Search engine exception');
        }

        // Decode to json
        $res = json_decode($res->getBody()->read(1024*1024));

        // Get result
        if ($res->hits->total) {
            $products = [];
            $buckets = $res->aggregations->by_domain->buckets;
            $length = count($buckets);
            for ($i = 0; $i < $length; $i++) {
                $product = $buckets[$i]->tops->hits->hits[0]->_source;
                $product->url = $this->buildTrackingUrl($product->url,
				'product-card', null, $product->domain);

		// TODO: Replace 'full' to 'thumbs/small' etc.
		if ($product->images) {
		    $product->thumbs = $this->cdn .
				       $product->images[0]->path;

		    $product->images = $this->cdn .
				       $product->images[0]->path;
		}

                $products[] = $product;
            }

            $results = ['products' => $products, 'total' => $length];
            return $this->success($results);
        } else {
            return $this->error('No result found');
        }
    }


    /**
     * Get ElasticSearch query string of single product.
     * @param $name
     * @param $brand
     * @param $category
     * @param $domain
     * @return string
     */
    /*
    private function getSingleProductQueryBody($name, $brand, $category, $domain)
    {
        $match_name = '{
            "match": {
                "name": {
                    "operator": "and",
                    "query": "'. $name .'"
                }
            }
        }';

        if ($brand || $category || $domain) {
            $extra_match = '';

            if ($brand)
                $extra_match = ',{ "match": { "brand": "'. $brand .'" } }';

            if ($category)
                $extra_match .= ',{ "match": { "categories": "'. $category .'" } }';

            if ($domain)
                $extra_match .= ',{ "match": { "domain": "'. $domain .'" } }';

            $query = '
            "query": {
                "bool": {
                    "must": [
                        { 
                            ' . $match_name . '
                            ' . $extra_match . '
                        }
                    ]
                }
            }
            ';
        } else {
            $query = '
            "query": 
                 '. $match_name .'
            ';
        }

        return $query;
    }
    */

    /**
     * * Get ElasticSearch query string of single/multiple products.
     * @param $name
     * @param $brand
     * @param $category
     * @param $domain
     */
    private function getProductsQueryBody($name, $brand, $domain, $category)
    {
        $tmp = explode('|', $name);
        if (count($tmp) > 1)
            $name = $tmp;

        // Always explode domain and brand into array

        if ($domain)
            $domain = explode('|', $domain);

        if ($brand)
            $brand = explode('|', $brand);

        // Single product name case, mode 3, 4, 5, 6
        if (!is_array($name)) {
            $match_name = '{
                "match": {
                    "name": {
                        "operator": "and",
                        "query": "' . $name . '"
                    }
                }
            }';

            $query_domain = '';
            if ($domain) {
                if (count($domain) > 1) {
                    // Mode 4
                    $query = [];
                    foreach ($domain as $d)
                        $query [] = '{ "term": { "domain": "' . $d . '"} }';
                    $query_domain = '{
                        "bool": {
                            "should": [ '. join(',', $query) .' ]
                        }
                    }';
                } else {
                    $query_domain = '{ "term": { "domain": "' . $domain . '" }';
                }
            }

            $query_brand = '';
            if ($brand) {
                if (count($brand) > 1) {
                    // Mode 5
                    $query = [];
                    // FIXME: brand may not be a normalized keywords in ES
                    foreach ($brand as $b)
                        $query [] = '{ "term": { "brand": "' . $b . '"} }';
                    $query_brand = '{
                        "bool": {
                            "should": [ '. join(',', $query) .' ]
                        }
                    }';
                } else {
                    $query_brand = '{ "term": { "brand": "' . $domain . '" }';
                }
            }

            $extra_query = '';
            if ($query_domain)
                $extra_query .= ',' . $query_domain;
            if ($query_brand)
                $extra_query .= ',' . $query_brand;

            if ($extra_query) {
                // Mode 4/5: single name, multiple domains/brand
                return '{
                    "bool": {
                        "must": [
                             '. $match_name . $extra_query . '
                        ]
                    }
                }';
            } else {
                // Single name, no domain or brand
                return $match_name;
            }
        } else {
            // Mode 1, 2: multiple names, single/multi brand/domain

            $query = [];
            $length = count($name);
            $domain_length = count($domain);
            $brand_length = count($brand);

            for ($i = 0; $i < $length; $i++) {
                $tmp = '"name": {
                     "query": "'. $name[$i]. '",
                     "operator": "and"
                }';

                if ($i < $domain_length)
                    // 1:1 mapping domains
                    $tmp .= ',"domain": "'. $domain[$i] .'"';
                else
                    // single domain, or partial name:domain mapping
                    $tmp .= ',"domain": "'. $domain[$domain_length - 1] .'"';

                if ($i < $brand_length)
                    // 1:1 mapping brands
                    $tmp .= ',"brand": "'. $brand[$i] .'"';
                else
                    // single brand, or partial name:brand mapping
                    $tmp .= ',"brand": "'. $brand[$brand_length - 1] .'"';

                $query[] = '{ 
                    "match": {
                        '. $tmp .'
                    } 
                }';
            }

            return '{
                "bool": {
                     "should": [
                         '.join(',', $query).'
                     ]
                }
            }';
        }
    }
}

