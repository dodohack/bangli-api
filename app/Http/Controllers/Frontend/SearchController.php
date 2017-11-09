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
    public function search(Request $request)
    {
	$text = $request->get('text'); // Query text
	$from = (int) $request->get('from', 0);  // offset of searched items
	$size = (int) $request->get('size', 20); // number of searched items

        if (!$this->es)
            return $this->error("No backend search engine available");

        if (empty($text))
            return $this->error("Search string should be non empty");

        $search_api = $this->es . '/_search';

	// Always double qoute $text
	if ($text[0] != '"') $text = '"' . $text . '"';

        $client = new Client();

	// Construct the body:
	// * Query if 'url', 'title', 'content' matches given text.
	// * Highlight matched txt with <tag1>, <tag2>...
	// * Only return 'url' and 'title' for matched entries
        $body = '
            {
              "from": ' . $from . ', 
	      "size": ' . $size . ',
              "query" : { "query_string": {
                              "fields": ["url", "title", "content"],
                              "query": ' . $text . '
                           }
               },
              "highlight" : {
                       "pre_tags" : ["<tag1>", "<tag2>"],
                       "post_tags" : ["</tag1>", "</tag2>"],
                       "fields" : {
                           "content" : {}
                       }
              },
              "_source" : ["url", "title"]
            }
            ';

        try {
            $res = $client->request('POST', $search_api, ['body' => $body]);
        } catch (ServerException $e) {
            return $this->error("Search engine exception");
        }

	// Decode to json
        $res = json_decode($res->getBody()->read(1024*1024));

	// Get result
	if($res->hits->total) {
	    $entities = [];
	    $hits = $res->hits->hits;
	    $length = count($hits);
	    for($i = 0; $i < $length; $i++) {
		$entities[] = ["url" => $hits[$i]->_source->url,
			      "title" => $hits[$i]->_source->title,
			      "content" => $hits[$i]->highlight->content[0]];
	    }

	    $results = ['entities' => $entities,
			'from'     => $from,
			'size'     => $size,
			'total'    => $res->hits->total];

	    return $this->success($results);
	} else {
	    return $this->error("No result found");
	}
    }
}
