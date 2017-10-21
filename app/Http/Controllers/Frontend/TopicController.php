<?php
/**
 * Frontend topic controller, supports various type of topics
 */

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;

use App\Models\FeTopic;
use App\Models\FeOffer;
use App\Models\FeViewRelatedTopic;
use App\Models\ViewTopicHasTopic;
use App\Models\ViewTopicHasPost;

class TopicController extends FeController
{
    /* Columns to be retrieved for topics list */
    private $topicsColumns = ['topics.id', 'topics.channel_id',
        'type_id', 'ranking', 'guid', 'logo',
        'title', 'title_cn', 'updated_at'];

    /* Relations to be queried with topic/topics */
    private $topicsRelations = [];


    // FIXME: If enabling querying relationship count, select the column of
    // FIXME: the main table will not work!!
    /* Retrieve number of offers related to given topic */
    private $relationCount = 'offers';

    /**
     * Get a list of published topics
     * @param Request $request
     * @return mixed a list of published topics
     */
    public function getTopics(Request $request)
    {
        $inputs = $request->all();
        $relations = null;

        if (isset($inputs['relations']))
            $relations = $this->setupRelations($inputs['relations']);

        $result = $this->getArrayEntitiesByKey($inputs, $relations,
            $this->relationCount, $this->topicsColumns, 'full');
        // FIXME: Error handling.
        return $this->success($request, json_encode($result));
    }

    /**
     * Get single/multiple group of listed published topics with filter key
     * as index of each returned group.
     * @param Request $request
     * @return mixed
     */
    public function getGroupTopics(Request $request)
    {
        $result = $this->getGroupedEntities($request->all(),
            null, $this->topicsColumns);

        return $this->success($request, json_encode($result));
    }

    // FIXME: Merge FeToic::topic_relations/topic_columns with
    // the definition relations/columns of data member.
    public function getTopic(Request $request, $guid)
    {
        $inputs = $request->all();
        $table  = $this->getEntityTable($inputs['etype']);

        $topic = $this->getEntityObj($inputs['etype'], 'guid', $guid, $table,
            FeTopic::topic_relations(), FeTopic::topic_columns(),
            $this->relationCount);

        if (!$topic) {
            // FIXME: Return content
            return response("Topic not found", 401);
        }

        // Get related offers
        $offers = Array();

        switch ($topic->type['slug']) {
            case TT_BRAND:
            case TT_MERCHANT:
            case TT_PRODUCTS:
                $offers = $topic->offers()->get();
                break;
            case TT_GENERIC:
            case TT_COUNTRY:
            case TT_CITY:
            case TT_ROUTE:
            case TT_PRODUCT:
                dd("TODO");
                break;
            default:
                break;
        }

        $topic['offers'] = $offers;

        $ret = [
            "etype"  => $inputs['etype'],
            "entity" => $topic
        ];

        return parent::successV2($inputs, json_encode($ret));
    }

    /**
     * Return top n series topics which will be used to query hot products,
     * hot topics and hot posts.
     * @param $tid
     * @param $series_type_slug
     * @param $num
     * @return mixed
     */
    protected function getHotSeriesTopics($tid, $series_type_slug, $num)
    {
        return ViewTopicHasTopic::where('topic1_id', $tid)
            ->where('t2_type_slug', $series_type_slug)
            ->orderBy('t2_ranking', 'desc')
            ->limit($num)
            ->get()->toArray();
    }

    /**
     * Return given n group of top topic series, with m items per group
     * @param $tid - Topic id of current showing page
     * @param $series_slug - Series topic type slug
     * @param $single_slug - Single topic type slug in the series
     * @param $num_group   - Max number of series
     * @param $num_item    - Max number of items per series
     * @param bool $needAffUrl - If need to check if aff_url should not be empty
     * @return null
     */
    protected function getHotTopics($series, $single_slug,
                                    $num_group, $num_item, $needAffUrl = false)
    {
        $cols = ['topic2_id', 't2_title', 't2_anchor_text', 't2_guid'];
        $deal_cols = ['topic2_id', 't2_title', 't2_anchor_text', 't2_guid',
            't2_aff_url'];

        // Do a copy of the first n element of array
        $ret = array_slice($series, 0, $num_group);

        // Get individual product per series
        if ($needAffUrl) {
            foreach ($ret as $k => $v) {
                $ret[$k]['children'] =
                    ViewTopicHasTopic::where('topic1_id', $v['topic2_id'])
                        ->where('t2_type_slug', $single_slug)
                        ->where('t2_aff_url', '!=', '')
                        ->orderBy('t2_ranking', 'desc')
                        ->limit($num_item)
                        ->get($deal_cols)->toArray();
            }
        } else {
            foreach ($ret as $k => $v) {
                $ret[$k]['children'] =
                    ViewTopicHasTopic::where('topic1_id', $v['topic2_id'])
                        ->where('t2_type_slug', $single_slug)
                        ->orderBy('t2_ranking', 'desc')
                        ->limit($num_item)
                        ->get($cols)->toArray();
            }
        }

        return $ret;
    }

    /**
     * Return related topics of current topic, the topic should be in same
     * category and same topic type.
     * @param $tid - Current topic id
     * @param $cat_ids - Current topic's categories ids
     * @param $ttype_id - Current topic type id
     * @param $num - max related topics to return
     * @return $ret - list of related topics
     */
    protected function getRelatedTopics($tid, $cat_ids, $ttype_id, $num = 20)
    {
        $cols = ['title', 'anchor_text', 'guid'];
        if (!$cat_ids || !count($cat_ids) || !$ttype_id) return [];

        return FeViewRelatedTopic::whereIn('cat_id', $cat_ids)
            ->where('topic_id', '!=', $tid)
            ->where('type_id', $ttype_id)
            ->orderBy('ranking', 'desc')
            ->limit($num)
            ->get($cols)->toArray();
    }

    /**
     * Return given number of posts belongs to given topic, sort by view count
     * @param $tid
     * @param $num_post
     */
    protected function getHotPosts($tid, $num_post)
    {
        $cols = ['post_id', 'title', 'published_at'];
        return ViewTopicHasPost::where('topic_id', $tid)
            ->orderBy('view', 'desc')
            ->limit($num_post)
            ->get($cols)->toArray();
    }

    /**
     * Return given number of posts belongs to given topic, sort by publish date
     * @param $tid
     * @param $num_post
     */
    protected function getLatestPosts($tid, $num_post)
    {
        $cols = ['post_id', 'title', 'published_at'];
        return ViewTopicHasPost::where('topic_id', $tid)
            ->orderBy('published_at', 'desc')
            ->limit($num_post)
            ->get($cols)->toArray();
    }

    /**
     * Get number of series post of this topic, and the posts also belongs to
     * a second topic type.
     * @param $tid
     * @param $series_slug
     * @param $num_group
     * @return $ret - series of posts within each topic
     */
    protected function getPostSeries($series, $num_group, $num_item)
    {
        $cols = ['post_id', 'title', 'published_at'];
        $ret = array_slice($series, 0, $num_group);
        foreach ($ret as $k => $v) {
            $ret[$k]['posts'] =
                TopicHasPostView::where('topic_id', $v['topic2_id'])
                    ->orderBy('view', 'desc')
                    ->limit($num_item)
                    ->get($cols)->toArray();
        }

        return $ret;
    }

    /**
     * Get all offers belongs to given topic
     * @param $topic_id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    protected function getOffers($topic_id)
    {
        $table = new FeTopic();
        return $table->where('id', $topic_id)->offers();
    }
}