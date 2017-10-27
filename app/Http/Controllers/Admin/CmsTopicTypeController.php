<?php
/**
 * Backend cms topic_type controller
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TopicType;

class CmsTopicTypeController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Get a topic_type
     */
    public function get(Request $request, $id)
    {
        $tt = TopicType::find($id)->toArray();
        return $this->response($tt, 'get topic type error');
    }

    /**
     * Create a new topic_type
     */
    public function post(Request $request)
    {
        $input = $request->except('id');

        $tt = TopicType::create($input)->toArray();

        return $this->response($tt, 'post topic type error');
    }

    /**
     * Update a topic_type
     */
    public function put(Request $request, $id)
    {
        $input = $request->except('id');

        $tt = TopicType::find($id)->update($input)->toArray();

        return $this->response($tt, 'put topic type error');
    }

    /**
     * Delete a topic_type
     */
    public function delete(Request $request, $id)
    {
        $numDeleted = TopicType::destroy($id);

        return $this->response($numDeleted, 'delete topic type error');
    }
}
