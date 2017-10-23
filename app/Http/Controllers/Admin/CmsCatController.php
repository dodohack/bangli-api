<?php
/**
 * Dashboard cms category controller, category related operations
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Models\Category;

class CmsCatController extends CmsController
{

    /**
     * Get a category
     */
    public function getCategory(Request $request, $id)
    {
        $json = Category::find($id)->toJson();
        return parent::success($request, $json);
    }

    /**
     * Create a new category
     */
    public function postCategory(Request $request)
    {
        $input = $request->except('id');

        $newTag = Category::create($input);

        if ($newTag) {
            return parent::success($request, json_encode($newTag));
        } else {
            return response('FAIL', 401);
        }
    }

    /**
     * Update a category
     */
    public function putCategory(Request $request, $id)
    {
        $input = $request->except('id');

        $newCat = Category::find($id)->update($input);

        if ($newCat) {
            return $this->getCategory($request, $id);
        } else {
            return response('FAIL', 401);
        }
    }

    /**
     * Delete a category
     */
    public function deleteCategory(Request $request, $id)
    {
        $numDeleted = Category::destroy($id);

        if ($numDeleted)
            return parent::success($request, $id);
        else
            return response('FAIL', 401);
    }
}
