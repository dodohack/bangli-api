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
    // FIXME: If enabling querying relationship count, select the column of
    // FIXME: the main table will not work!!
    /* Retrieve number of offers related to given topic */
    private $relationCount = 'offers';

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

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
            $relations = $this->getRelations($inputs['relations']);

        $topics = $this->getEntitiesByKey($inputs, $relations,
            $this->relationCount, null, 'full');

        return $this->response($topics, 'get topics error');
    }

    /**
     * Get single/multiple group of listed published topics with filter key
     * as index of each returned group.
     * @param Request $request
     * @return mixed
     */
    public function getGroupTopics(Request $request)
    {
        $topicGroups = $this->getGroupedEntities($request->all(),
            null, null);

        return $this->success($topicGroups);
    }

    /**
     * Get a single topic
     * @param Request $request
     * @param $guid
     * @return mixed
     */
    // FIXME: Merge FeTopic::topic_relations/topic_columns with
    // the definition relations/columns of data member.
    public function getTopic(Request $request, $guid)
    {
        $topic = $this->getEntity('guid', $guid, null,
            ['offers'], null, $this->relationCount);

        return $this->response($topic, 'topic not found');
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
    private function getRelatedTopics($tid, $cat_ids, $ttype_id, $num = 20)
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
    private function getHotPosts($tid, $num_post)
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
    private function getLatestPosts($tid, $num_post)
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
    private function getPostSeries($series, $num_group, $num_item)
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
    private function getOffers($topic_id)
    {
        $table = new FeTopic();
        return $table->where('id', $topic_id)->offers();
    }
}
