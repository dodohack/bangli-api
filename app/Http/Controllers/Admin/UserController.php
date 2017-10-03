<?php
/**
 * Dashboard user controller
 * Shared by all domains.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\UserAddress;
use App\Models\UserBabyProfile;
use App\Models\UserShopProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\JWTAuth;

use App\Models\User;
use App\Http\Controllers\Traits\PaginatorTrait;


class UserController extends Controller
{
    use PaginatorTrait;

    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * Return a list of users, no need to validate incoming parameters
     * cause this route is protected by middleware.
     * Parameters:
     *  - page:     current page index
     *  - role_id:  the id of which role group of users
     *  - per_page: number of users per page
     *
     * @param Request $request
     * @return object $json: jsonified pagination
     */
    public function getUsers(Request $request)
    {
        /* Current page */
        $curPage = $request->has('page') ? intval($request->input('page')) : 0;
        /* User role/ role_id */
        $roleId =  $request->has('role_id') ? intval($request->input('role_id')) : 0;
        $role   =  $request->has('role') ? intval($request->input('role')) : null;
        /* Number of users per page, get it from input, default is 20 */
        $perPage = $request->has('per_page') ? intval($request->input('per_page')) : 20;

        if ($role && $roleId)
            return response('You cannot specify both role and role_id', 401);

        /* Number of skipped records for current page */
        $skipNum = ($curPage - 1) * $perPage;

        if ($roleId === 0 && !$role)
        {
            $total = User::count();
            $users = User::skip($skipNum)->take($perPage)->get();
        } else if ($roleId) {
            $total = User::where('role_id', $roleId)->count();
            $users = User::where('role_id', $roleId)->skip($skipNum)
                ->take($perPage)->get();
        } else if ($role) {
            $total = User::where('role', $role)->count();
            $users = User::where('role', $role)->skip($skipNum)
                ->take($perPage)->get();
        }

        $paginator = $this->paginator($total, $curPage, $perPage, $users->count());

        $ret = ["users" => $users->toArray(), "paginator" => $paginator];
        $json = json_encode($ret);
        
        /* Return JSONP or AJAX data */
        return parent::success($request, $json);
    }
    
    public function putUsers(Request $request) {
        return response('Unimplemented API', 401);
    }

    public function deleteUsers(Request $request) {
        return response('Unimplemented API', 401);
    }    

    /**
     * Return a list of users who can edit a post
     */
    private function getAuthors(Request $request)
    {
        /* Query table 'permissions' via table 'roles' from table 'user' */
        $json = User::whereHas('role.permissions', function ($query) {
            $query->where('name', 'edit_own_post');
        })->with('role')->get()->toJson();


        return parent::success($request, $json);
    }

    /**
     * Returen a list of users who can edit any post
     * @param Request $request
     * @return string
     */
    private function getEditors(Request $request)
    {
        /* Query table 'permissions' via table 'roles' from table 'user' */
        $json = User::whereHas('role.permissions', function ($query) {
            $query->where('name', 'edit_post');
        })->with('role')->get()->toJson();

        /* Return JSONP or AJAX data */
        return parent::success($request, $json);
    }

    /**
     * Return roles and users per role which is used to display the nav-bar
     * on user list page.
     * @param Request $request  - incoming request
     * @return object $json     - jsonified pagination
     */
    public function getRoles(Request $request)
    {
        /* Query column 'role' and count of occurrence of 'role' */
        /*
        $roleIds = DB::table('users')->select(DB::raw('role_id, COUNT(*) as count'))
            ->groupBy('role_id')->orderBy('count', 'desc')->get();
        */

        /* Query role name */
        $json = Role::get(['id','name','display_name'])->toJson();

        /* Return JSONP or AJAX data */
        return parent::success($request, $json);
    }

    /**
     * Retrieve a user with detailed profiles
     * @param Request $request
     * @param $uuid
     * @return object $json
     */
    public function getUser(Request $request, $uuid)
    {
        $myUuid = $this->jwt->getPayload()->get('sub');
        if ($myUuid !== $uuid) {
            /* Authenticate current user if it is not get my detail */
            $user = $this->jwt->authenticate();
            if (!$user->hasRole(['administrator', 'shop_manager'])) {
                return response('Unauthorized', 401);
            }
        }
        
        $tables = ['role', 'shopProfile', 'babyProfiles', 'addresses'];

        $json = User::where('uuid', $uuid)->with($tables)
            ->first()->toJson();

        /* Return JSONP or AJAX data */
        return parent::success($request, $json);
    }

    public function postUser(Request $request)
    {
        return response('Unimplemented API', 401);
    }

    public function putUser(Request $request, $uuid)
    {
        $body = json_decode($request->getContent(), true);

        $shop_profile  = $body['shop_profile'];
        $baby_profiles = $body['baby_profiles'];
        $addresses     = $body['addresses'];
        unset($body['shop_profile']);
        unset($body['baby_profiles']);
        unset($body['addresses']);
        /* This is a pivot record which is not going be stored directly */
        unset($body['role']);

        /* All relationships are striped, this is a bare user record */
        $user = $body;

        if ($user && $user['id']) {
            unset($user['uuid'], $user['updated_at']);
            User::where('id', $user['id'])->update($user);
        }

        if ($shop_profile && $shop_profile['id']) {
            unset($shop_profile['updated_at']);
            UserShopProfile::where('id', $shop_profile['id'])->update($shop_profile);
        }

        foreach ($addresses as $addr) {
            if ($addr['id']) {
                unset($addr['updated_at']);
                UserAddress::where('id', $addr['id'])->update($addr);
            }
        }

        foreach ($baby_profiles as $baby) {
            if ($baby['id']) {
                unset($baby['updated_at']);
                UserBabyProfile::where('id', $baby['id'])->update($baby);
            }
        }

        /* Return ok for now */
        return $this->getUser($request, $uuid);
    }

    public function deleteUser(Request $request, $uuid)
    {
        return response('Unimplemented API', 401);
    }
}
