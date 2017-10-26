<?php
/**
 * Shop controller, base class of all cms controllers
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\JWTAuth;

use App\Http\Controllers\Traits\PaginatorTrait;
use App\Models\Role;

class SysController extends Controller
{
    /**
     * Get a group of system related attributes to cache on client at app
     * start up, includes:
     * roles, 
     */
    public function getAttributes(Request $request)
    {
        // Available user roles
        $roles = Role::get()->toArray();

        // Available thumbnail configs
        $thumbs = config('filesystems.thumbs');

        $positions = config('ads.positions');

        $array = compact('roles', 'thumbs', 'positions');

        return parent::successReq($request, $array);
    }
}
