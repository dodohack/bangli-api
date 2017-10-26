<?php
/**
 * Frontend menu setting controller
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Menu;

class FeMenuController extends Controller
{

    /**
     * Get list of menus
     */
    public function getFeMenus(Request $request)
    {
        $menu = Menu::get();
        return parent::successReq($request, $menu);
    }

    /**
     * Get a menu
     */
    public function getFeMenu(Request $request, $id)
    {
        $menu = Menu::find($id);
        return parent::successReq($request, $menu);
    }

    /**
     * Create a new menu
     */
    public function postFeMenu(Request $request)
    {
        $input = $request->except('id');

        $newMenu = Menu::create($input);

        if ($newMenu) {
            return parent::successReq($request, $newMenu);
        } else {
            return parent::errorReq($request, 'post femenu error');
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
            return parent::successReq($request, $newMenu);
        } else {
            return parent::errorReq($request, 'put femenu error');
        }
    }

    /**
     * Delete a menu
     */
    public function deleteFeMenu(Request $request, $id)
    {
        $numDeleted = Menu::destroy($id);

        if ($numDeleted)
            return parent::successReq($request, $id);
        else
            return parent::errorreq($request, 'delete femenu error');
    }
}
