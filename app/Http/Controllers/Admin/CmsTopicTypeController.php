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
        $tt = TopicType::find($id);
        return parent::successReq($request, $tt);
    }

    /**
     * Create a new topic_type
     */
    public function post(Request $request)
    {
        $input = $request->except('id');

        $new = TopicType::create($input);

        if ($new) {
            return parent::successReq($request, $new);
        } else {
            return parent::errorReq($request, 'post topic type fail');
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
            return parent::successReq($request, $new);
        } else {
            return parent::errorReq($request, 'put topic type error');
        }
    }

    /**
     * Delete a topic_type
     */
    public function delete(Request $request, $id)
    {
        $numDeleted = TopicType::destroy($id);

        if ($numDeleted)
            return parent::successReq($request, $id);
        else
            return parent::errorReq($request, 'delete topic type error');
    }
}
