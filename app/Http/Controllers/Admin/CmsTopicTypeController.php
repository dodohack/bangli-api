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

    /**
     * Get a topic_type
     */
    public function get(Request $request, $id)
    {
        $json = TopicType::find($id)->toJson();
        return parent::success($request, $json);
    }

    /**
     * Create a new topic_type
     */
    public function post(Request $request)
    {
        $input = $request->except('id');

        $new = TopicType::create($input);

        if ($new) {
            return parent::success($request, json_encode($new));
        } else {
            return response('FAIL', 401);
        }
    }

    /**
     * Update a topic_type
     */
    public function put(Request $request, $id)
    {
        $input = $request->except('id');

        $new = TopicType::find($id)->update($input);

        if ($new) {
            return $this->get($request, $id);
        } else {
            return response('FAIL', 401);
        }
    }

    /**
     * Delete a topic_type
     */
    public function delete(Request $request, $id)
    {
        $numDeleted = TopicType::destroy($id);

        if ($numDeleted)
            return parent::success($request, $id);
        else
            return response('FAIL', 401);
    }
}
