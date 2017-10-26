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

    /**
     * Get a tag
     */
    public function getTag(Request $request, $id)
    {
        $tag = Tag::where('id', $id)->first();

        return parent::successReq($request, $tag);
    }
    
    /**
     * Create a new tag
     */
    public function postTag(Request $request)
    {
        $input = $request->except('id');

        $newTag = Tag::create($input);

        return parent::responseReq($request, $newTag, 'post tag error');
    }

    /**
     * Update a tag
     */
    public function putTag(Request $request, $id)
    {
        $input = $request->except('id');

        $newTag = Tag::find($id)->update($input);

        return parent::responseReq($request, $newTag, 'put tag error');
    }

    /**
     * Delete a tag
     */
    public function deleteTag(Request $request, $id)
    {
        $numDeleted = Tag::destroy($id);

        return parent::responseReq($request, $numDeleted, 'delete tag error');
    }
}
