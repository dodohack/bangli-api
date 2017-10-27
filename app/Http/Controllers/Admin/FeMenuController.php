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
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Get list of menus
     */
    public function getFeMenus(Request $request)
    {
        $menu = Menu::get()->toArray();
        return $this->response($menu, 'get femenus error');
    }

    /**
     * Get a menu
     */
    public function getFeMenu(Request $request, $id)
    {
        $menu = Menu::find($id)->toArray();
        return $this->response($menu, 'get femnu error');
    }

    /**
     * Create a new menu
     */
    public function postFeMenu(Request $request)
    {
        $input = $request->except('id');

        $newMenu = Menu::create($input)->toArray();

        return $this->response($newMenu, 'post femenu error');
    }

    /**
     * Update a menu
     */
    public function putFeMenu(Request $request, $id)
    {
        $input = $request->except('id');

        $newMenu = Menu::find($id)->update($input)->toArray();

        return $this->response($newMenu, 'put femenu error');
    }

    /**
     * Delete a menu
     */
    public function deleteFeMenu(Request $request, $id)
    {
        $numDeleted = Menu::destroy($id);

        return $this->response($numDeleted, 'delete femenu error');
    }
}
