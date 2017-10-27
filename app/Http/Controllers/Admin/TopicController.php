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
    /* Retrieve number of offers related to given topic */
    private $relationCount = 'offers';

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Return a list of topics, no need to validate incoming parameters
     * cause this route is protected by middleware.
     */
    public function getTopics(Request $request)
    {
        $topics = $this->getEntities($request->all(),
            null, $this->relationCount, null);

        return $this->response($topics, 'get topics error');
    }

    /**
     * Update multiple topics
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function putTopics(Request $request)
    {
        return $this->error('API unimplemented');
    }

    /**
     * Move multiple topics into trash or physically delete topics from trash
     * @param Request $request
     * @return
     */
    public function deleteTopics(Request $request)
    {
        $ids = $request->get('ids');
        $numDeleted = $this->deleteEntities($ids);

        return $this->response($numDeleted, 'delete topics error');
    }

    /**
     * Return topic statuss
     *
     * @return object $json: jsonified pagination
     */
    public function getStatus()
    {
        $status = Topic::select(DB::raw('status, COUNT(*) as count'))
            ->groupBy('status')->get();

        return $this->response($status, 'get topic status error');
    }

    /**
     * Get a topic
     * @param Request $request
     * @param $id
     * @return object
     */
    public function getTopic(Request $request, $id)
    {
        $topic = $this->getEntity('id', $id,
            null, null, null, $this->relationCount);

        return $this->response($topic, 'get topic error');
    }

    /**
     * Update topic by given id/guid
     * @param Request $request
     * @param $id - topic id/guid to be updated
     * @return object
     */
    public function putTopic(Request $request, $id)
    {
        $topic = $this->putEntity($request->all(), 'id', $id,
            null, null, $this->relationCount);

        return $this->response($topic, 'put topic error');
    }

    /**
     * Create a new topic
     * @param Request $request
     * @return object
     */
    public function postTopic(Request $request)
    {
        $topic = $this->postEntity($request->all());

        return $this->response($topic, 'post topic error');
    }

    /**
     * Move a topic to trash or physically delete a topic from trash
     * @param Request $request
     * @param $id
     * @return Topic | bool
     */
    public function deleteTopic(Request $request, $id)
    {
        $deleted = $this->deleteEntity('id', $id);

        return $this->response($deleted, 'delete topic error');
    }
}
