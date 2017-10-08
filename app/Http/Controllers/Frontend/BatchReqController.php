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
     *     {"params": "key=lastest_post;etype=post;channel=shopping;per_page=6;sort_by=date;order=desc"},
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

            $result[$idx++] =
                $this->getArrayEntitiesByKey($group->inputs, $relations, null, 'none');
        }

        return $this->success($request, json_encode($result));
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

    /**
     * Sanitize incoming relations string, return relations in array
     * FIXME: Hardcoded relationship
     * @param $relationString
     * @return array
     */
    private function setupRelations($relationString) {
        $relations = [];
        if (!$relationString) return null;

        // We expect incoming relations are separated by ','
        $tokens = explode(",", $relationString);
        foreach ($tokens as $rel) {
            switch($rel) {
                case ETYPE_TOPIC:
                    /**
                     * FIXME: We got QueryException by specifying the columns
                     * of the relationship. I have also tried to manually write
                     * the closure
                     * $db->with(array(topics => function($query) {
                     *    $query->select('id', 'guid', ...);
                     * }));
                     * to query relationship columns, which does hit the same error.
                     *
                     * After we solve this issue, we can eliminate lots of definition
                     * of views whose main purpose is to limit the columns of a
                     * relationship.
                     *
                     * SQLSTATE[23000]: Integrity constraint violation: 1052 Column 'id' in field list is ambiguous (SQL: select `id`, `guid`, `logo`, `title`, `topic_has_offer`.`offer_id` as `pivot_offer_id`, `topic_has_offer`.`topic_id` as `pivot_topic_id` from `topics` inner join `topic_has_offer` on `topics`.`id` = `topic_has_offer`.`topic_id` where `topic_has_offer`.`offer_id` in (7459, 7769, 7771, 7772, 7930, 7947))
                     *
                     */
                    array_push($relations, 'topics:id,guid,logo,title');
                    break;
                case ETYPE_OFFER:
                    array_push($relations, 'offers');
                    break;
                case ETYPE_POST:
                    array_push($relations, 'posts');
                    break;
                case ETYPE_PAGE:
                    array_push($relations, 'pages');
                    break;
                case ETYPE_ATTACHMENT:
                    array_push($relations, 'attachments');
                    break;
                case ETYPE_COMMENT:
                    array_push($relations, 'comments');
                    break;
            }
        }

        if (count($relations))
            return $relations;
        else
            return null;
    }
}