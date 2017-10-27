<?php
/**
 * Dashboard cms tag controller, tag related operations  
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tag;

class CmsTagController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Get a tag
     */
    public function getTag(Request $request, $id)
    {
        $tag = Tag::find($id)->toArray();

        return $this->response($tag, 'get tag error');
    }
    
    /**
     * Create a new tag
     */
    public function postTag(Request $request)
    {
        $input = $request->except('id');

        $newTag = Tag::create($input)->toArray();

        return $this->response($newTag, 'post tag error');
    }

    /**
     * Update a tag
     */
    public function putTag(Request $request, $id)
    {
        $input = $request->except('id');

        $newTag = Tag::find($id)->update($input)->toArray();

        return $this->response($newTag, 'put tag error');
    }

    /**
     * Delete a tag
     */
    public function deleteTag(Request $request, $id)
    {
        $numDeleted = Tag::destroy($id);

        return $this->response($numDeleted, 'delete tag error');
    }
}
