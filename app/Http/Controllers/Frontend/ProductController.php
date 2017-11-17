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

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->es = env('ES_ENDPOINT') . '/' . env('ES_PRODUCT');
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

    /*
     * Return a list of published advertisements
     * Request example:
     * <endpoint>/products?
     *  <name=product name string>
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
        $name     = $request->get('name'); // Product name, required
        $brand    = $request->get('brand'); // Optional
        $category = $request->get('category'); // Optional
        $domain   = $request->get('domain'); // Optional

        $client = new Client();
        $search_api = $this->es . '/_search';

        if (!$name)
            return $this->error("Product name is missing");

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
                    "must:" [
                        { 
                            "match": {
                                "name": {
                                    "operator": "and",
                                    "query": "'. $name .'"
                                }
                            }
                            ' . $extra_match . '
                        }
                    ]
                }
            }
            ';
        } else {
            $query = '
            "query": {
                "match": {
                    "name": {
                        "operator": "and",
                        "query": "'. $name .'"
                    }
                }
            }
            ';
        }

        // Request body with aggregate by domain with top 1 result of each domain
        $body = '
        {
            ' . $query . ',
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
                $product->url = $this->buildTrackingUrl($product->url, $product->domain);
                $products[] = $product;
            }

            $results = ['products' => $products, 'total' => $length];
            return $this->success($results);
        } else {
            return $this->error('No result found');
        }
    }
}
