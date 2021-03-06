<?php
/**
 * Dashboard cms category controller, category related operations
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Models\Category;
use App\Models\PostHasCategory;
use App\Models\TopicHasCategory;
use App\Models\OfferHasCategory;

class CmsCatController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Get categories
     */
    public function getCategories(Request $request)
    {
        $ret = Category::all()->toArray();

        return $this->response($ret, 'get category error');
    }


    /**
     * Get a category
     */
    public function getCategory(Request $request, $id)
    {
        $ret = Category::find($id)->toArray();

        return $this->response($ret, 'get category error');
    }

    /**
     * Create a new category
     */
    public function postCategory(Request $request)
    {
        $inputs = $request->except('id');

        $newCat = Category::create($inputs)->toArray();

        return $this->response($newCat, 'post category fail');
    }

    /**
     * Update a category
     */
    public function putCategory(Request $request, $id)
    {
        $inputs = $request->except('id');

        $record = Category::find($id);
        $record->update($inputs);
        $cat = $record->toArray();

        return $this->response($cat, 'put category fail');
    }

    /**
     * Delete a category:
     * If a child category is deleted, all its relations will be auto added to
     * its parent category.
     * If it is a root category and there is relationship with it, it can't be
     * deleted(this is done by database FK constraint).
     */
    public function deleteCategory(Request $request, $id)
    {
        $cat = Category::find($id);

        // Move relationships to its parent first
        if ($cat->parent_id != 0) {
            PostHasCategory::where('cat_id', $id)->update(['cat_id' => $cat->parent_id]);
            TopicHasCategory::where('cat_id', $id)->update(['cat_id' => $cat->parent_id]);
            OfferHasCategory::where('cat_id', $id)->update(['cat_id' => $cat->parent_id]);
        }

        // Now we can safely destroy the category when relationships are removed
        $deleted = Category::destroy($id);

        if ($deleted)
            return $this->success(['id' => $id]);

        return $this->error('delete category error');
    }


}
