<?php
/**
 * Dashboard cms tag controller, tag related operations  
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Models\Cms\Tag;

class CmsTagController extends CmsController
{

    /**
     * Get a tag
     */
    public function getTag(Request $request, $id)
    {
        $json = Tag::find($id)->toJson();
        return parent::success($request, $json);
    }
    
    /**
     * Create a new tag
     */
    public function postTag(Request $request)
    {
        $input = $request->except('id');

        $newTag = Tag::create($input);

        if ($newTag) {
            return parent::success($request, json_encode($newTag));
        } else {
            return response('FAIL', 401);
        }
    }

    /**
     * Update a tag
     */
    public function putTag(Request $request, $id)
    {
        $input = $request->except('id');

        $newTag = Tag::find($id)->update($input);

        if ($newTag) {
            return $this->getTag($request, $id);
        } else {
            return response('FAIL', 401);
        }
    }

    /**
     * Delete a tag
     */
    public function deleteTag(Request $request, $id)
    {
        $numDeleted = Tag::destroy($id);

        if ($numDeleted)
            return parent::success($request, $id);
        else
            return response('FAIL', 401);
    }
}
