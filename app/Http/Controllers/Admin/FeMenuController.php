<?php
/**
 * Frontend menu setting controller
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Models\Menu;

class FeMenuController extends CmsController
{

    /**
     * Get list of menus
     */
    public function getFeMenus(Request $request)
    {
        $json = Menu::get()->toJson();
        return parent::success($request, $json);
    }

    /**
     * Get a menu
     */
    public function getFeMenu(Request $request, $id)
    {
        $json = Menu::find($id)->toJson();
        return parent::success($request, $json);
    }

    /**
     * Create a new menu
     */
    public function postFeMenu(Request $request)
    {
        $input = $request->except('id');

        $newMenu = Menu::create($input);

        if ($newMenu) {
            return parent::success($request, json_encode($newMenu));
        } else {
            return response('FAIL', 401);
        }
    }

    /**
     * Update a menu
     */
    public function putFeMenu(Request $request, $id)
    {
        $input = $request->except('id');

        $newMenu = Menu::find($id)->update($input);

        if ($newMenu) {
            return $this->getFeMenu($request, $id);
        } else {
            return response('FAIL', 401);
        }
    }

    /**
     * Delete a menu
     */
    public function deleteFeMenu(Request $request, $id)
    {
        $numDeleted = Menu::destroy($id);

        if ($numDeleted)
            return parent::success($request, $id);
        else
            return response('FAIL', 401);
    }
}
