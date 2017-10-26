<?php
/**
 * Dashboard cms topic controller
 * Shared by all domains.
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\EntityController;

use App\Models\Topic;


class TopicController extends EntityController
{

    /* Columns to be retrieved for topics list */
    private $topicsColumns = ['topics.id', 'editor_id', 'channel_id',
        'type_id', 'location_id', 'lock', 'ranking', 'status', 'guid',
        'title', 'title_cn', 'created_at', 'updated_at'];

    /* Relations to be queried with topic/topics */
    private $topicsRelations = ['editor', 'categories', 'topics',
        'channel', 'type', 'statistics', 'activities'];
    private $topicRelations = ['editor', 'images', 'categories', 'topics',
        'offers', 'channel', 'type', 'location', 'revisions', 'statistics'];

    /* Retrieve number of offers related to given topic */
    private $relationCount = 'offers';

    /**
     * Return a list of topics, no need to validate incoming parameters
     * cause this route is protected by middleware.
     */
    public function getTopics(Request $request)
    {
        return $this->getEntitiesReq($request,
            $this->topicsRelations, $this->relationCount, $this->topicsColumns);
    }

    public function putTopics(Request $request)
    {
        return response('unimplemented API', 401);
    }

    public function deleteTopics(Request $request)
    {
        return $this->deleteEntitiesReq($request);
    }

    /**
     * Return topic statuss
     *
     * @param Request $request
     * @return object $json: jsonified pagination
     */
    public function getStates(Request $request)
    {
        $status = Topic::select(DB::raw('status, COUNT(*) as count'))
            ->groupBy('status')->get();

        return $this->responseReq($request, $status, 'get topic status error');
    }

    /**
     * Get a topic
     * @param Request $request
     * @param $id
     * @return object
     */
    public function getTopic(Request $request, $id)
    {
        return $this->getEntityReq($request, 'id', $id,
            null/* table */, $this->topicRelations, null/* columns */,
            $this->relationCount);
    }

    /**
     * Update topic by given id/guid
     * @param Request $request
     * @param $id - topic id/guid to be updated
     * @return object
     */
    public function putTopic(Request $request, $id)
    {
        $inputs = $request->all();

        // Set editor_id if client does not set, this is required.
        // Affiliate related cronjob will look into editor_id as a flag of
        // if a topic is modified manually or not.
        $editor_id = $this->guard()->user()->id;
        if (!isset($inputs['editor_id'])) $inputs['editor_id'] = $editor_id;

        return $this->putEntity($inputs['etype'], $inputs, 'id', $id,
            $this->topicRelations, null/* columns */, $this->relationCount);
    }

    /**
     * Create a new topic
     * @param Request $request
     * @return object
     */
    public function postTopic(Request $request)
    {
        $inputs = $request->all();

        // Set editor_id if client does not set, this is required.
        // Affiliate related cronjob will look into editor_id as a flag of
        // if a topic is modified manually or not.
        $editor_id = $this->guard()->user()->id;
        if (!isset($inputs['editor_id'])) $inputs['editor_id'] = $editor_id;

        return $this->postEntity($inputs['etype'], $inputs);
    }

    /**
     * Move a topic to trash by uuid
     * @param Request $request
     * @param $id
     * @return Topic
     */
    public function deleteTopic(Request $request, $id)
    {
        return $this->deleteEntityReq($request, 'id', $id);
    }
}
