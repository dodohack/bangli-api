<?php
/**
 * Frontend batch request controller, client can request any groups of records
 * within 1 request. For example, a request to get 10 latest posts of shopping
 * channel, 10 featured deals, 12 featured websites topic and 12 featured brand
 * topics in one go.
 */
namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\FeTopic;
use App\Models\FePost;
use App\Models\Post;
use App\Models\ViewTopicHasTopic;
use App\Models\ViewTopicHasPost;
use App\Models\FeViewRelatedTopic;

class BatchReqController extends FeController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * The basic idea of the batch request is that each group in the request
     * is a stand alone single request with a key, so each group can be treated
     * as a standard request and shares the same underlying methods.
     *
     * LEGEND:
     *  group:  Indicate this is a group request to this API
     *  params: Request parameters of each group, each parameter and value is
     *          seperated by ';' instead of '&'.
     *
     * Format:
     *  /batch?group=[{"params": "..."}, {...}, ...];
     *
     * E.g:
     *  This request gets 2 groups of records:
     *  1. Group 1 is returned with json key 'latest_post', client uses the
     *     key to retrieve returned data. this group request returns 6 latest
     *     posts in shopping channel on success.
     *  2. Group 2 is returned with json key 'featured_deal', this group request
     *     returns 10 top ranking deal topic in shopping channel on success.
     *
     *  RESTful request:
     *
     *  /batch?group=[
     *     {"params": "key=lastest_post;etype=post;channel=shopping;topic=xxx;per_page=6;sort_by=date;order=desc"},
     *     {"params": "key=featured_topic;etype=topic;channel=shopping;per_page=10;sort_by=ranking;order=desc;relations=offer,post"},
     *     ...
     * ];
     * @param Request $request
     * @return string - json data
     */
    public function get(Request $request)
    {
        // Decode parameter from json string
        $groups = json_decode($request->input("group"));

        // Decode parameters in each group
        $this->convertParam2Input($groups);

        // Retrieve entities using standard method
        $result = [];
        $idx    = 0;
        foreach($groups as $group) {

            // If we need to get relations with the request
            $relations = null;
            if (isset($group->inputs['relations']))
                $relations = $this->setupRelations($group->inputs['relations']);

            // Always count number of offers of a gopic
            $relCount = null;
            if ($group->inputs['etype'] == ETYPE_TOPIC)
                $relCount = 'offers';

            // Entity type for each group should be set
            $this->etype = $group->inputs['etype'];

            $result[$idx] =
                $this->getEntitiesByKey($group->inputs, $relations,
                    $relCount, null, 'full');

            // Each group should be associated with etype
            $result[$idx]['etype'] = $this->etype;

            $idx++;
        }

        // This is the only place we do not need to return with toplevel etype
        return $this->success($result);
    }

    /**
     * Convert 'params' from each group to standard request input
     * @param $groups - groups of request, by reference
     */
    private function convertParam2Input($groups)
    {
        foreach($groups as $group) {
            $params = explode(";", $group->params);
            foreach ($params as $param) {
                $kv = explode("=", $param);
                $k = $kv[0];
                $v = $kv[1];
                $group->inputs[$k] = $v;
            }
        }
    }

}