<?php
/**
 * Dashboard cms topic controller
 * Shared by all domains.
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Models\Cms\Topic;


class TopicController extends CmsController
{

    /* Columns to be retrieved for topics list */
    private $topicsColumns = ['cms_topics.id', 'editor_id', 'channel_id',
        'type_id', 'location_id', 'lock', 'ranking', 'state', 'guid', 'title',
        'created_at', 'updated_at'];

    /* Relations to be queried with topic/topics */
    private $topicsRelations = ['editor', 'categories', 'topics',
        'channel', 'type', 'statistics', 'activities'];
    private $topicRelations = ['editor', 'images', 'categories', 'topics',
        'channel', 'type', 'location', 'revisions', 'statistics'];

    /**
     * Return a list of topics, no need to validate incoming parameters
     * cause this route is protected by middleware.
     */
    public function getTopics(Request $request)
    {
        return $this->getEntitiesReq($request,
            $this->topicsRelations, $this->topicsColumns);
    }

    public function putTopics(Request $request)
    {
        return response('unimplemented API', 401);
    }

    public function deleteTopics(Request $request)
    {
        return response('unimplemented API', 401);
    }

    /**
     * Return topic states
     *
     * @param Request $request
     * @return object $json: jsonified pagination
     */
    public function getStates(Request $request)
    {
        return $this->getEntityStates($request, 'cms_topics');
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
            null/* table */, $this->topicRelations, null/* columns */);
    }

    /**
     * Update topic by given guid
     * @param Request $request
     * @param $guid - topic guid to be updated
     * @return object
     */
    public function putTopic(Request $request, $id)
    {
        return $this->putEntityReq($request, 'id', $id,
                                   $this->topicRelations, null/* columns */);
    }

    /**
     * Create a new topic
     * @param Request $request
     * @return object
     */
    public function postTopic(Request $request)
    {
        return $this->postEntityReq($request);
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