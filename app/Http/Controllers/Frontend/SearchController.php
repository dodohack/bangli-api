<?php
/**
 * Frontend search controller
 */

namespace App\Http\Controllers\Frontend;

use GuzzleHttp\Exception\ServerException;
Use GuzzleHttp\Client;
use Illuminate\Http\Request;


class SearchController extends FeController
{
    // Elasticsearch endpoint with index
    protected $es;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->es = env('ES_ENDPOINT');
    }

    /**
     * get search result for given search string, we will do a RESTful post
     * to the elasticsearch api endpoint
     * @param Request $request
     * @param string $text - search text
     * @return json - search result
     */
    public function get(Request $request, string $text)
    {
        if (!$this->es)
            return $this->error("No backend search engine available");

        if (empty($text))
            return $this->error("Search string should be non empty");

        $search_api = $this->es . '/_search';

        $client = new Client();

        $body = '
            {
              "query" : { "query_string": {
                              "fields": ["url", "title", "content"],
                              "query": "' . $text . '"
                           }
               },
              "highlight" : {
                       "pre_tags" : ["<tag1>", "<tag2>"],
                       "post_tags" : ["</tag1>", "</tag2>"],
                       "fields" : {
                           "content" : {}
                       }
              }
            }
            ';

        try {
            $res = $client->request('POST', $search_api, ['body' => $body]);
        } catch (ServerException $e) {
            return $this->error("Search engine exception");
        }

        return json_decode($res->getBody()->read(1024*1024));
    }
}