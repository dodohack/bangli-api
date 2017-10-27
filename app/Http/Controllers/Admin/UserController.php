<?php
/**
 * Dashboard user controller
 * Shared by all domains.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\JWTAuth;

use App\Models\User;
use App\Http\Controllers\Traits\PaginatorTrait;


class UserController extends Controller
{
    use PaginatorTrait;

    public function __construct(Request $request)
    {
        parent::__construct($request);
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
        $curPage = intval($request->get('page', 0));
        /* User role/ role_id */
        $roleId =  intval($request->get('role_id', 0));
        $role   =  $request->get('role');
        /* Number of users per page, get it from input, default is 20 */
        $perPage = intval($request->get('per_page', 20));

        if ($role && $roleId)
            return $this->error('You cannot specify both role and role_id');

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

        return $this->success($ret);
    }
    
    public function putUsers(Request $request) {
        return $this->error('Unimplemented API');
    }

    public function deleteUsers(Request $request) {
        return $this->error('Unimplemented API');
    }    

    /**
     * Return a list of users who can edit a post
     */
    private function getAuthors(Request $request)
    {
        /* Query table 'permissions' via table 'roles' from table 'user' */
        $ret = User::whereHas('role.permissions', function ($query) {
            $query->where('name', 'edit_own_post');
        })->with('role')->get()->toArray();


        return $this->response($ret, 'get authors error');
    }

    /**
     * Returen a list of users who can edit any post
     * @param Request $request
     * @return string
     */
    private function getEditors(Request $request)
    {
        /* Query table 'permissions' via table 'roles' from table 'user' */
        $ret = User::whereHas('role.permissions', function ($query) {
            $query->where('name', 'edit_post');
        })->with('role')->get()->toArray();

        return $this->response($ret, 'get editors error');
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
        $ret = Role::get(['id','name','display_name'])->toArray();

        return $this->response($ret, 'get roles error');
    }

    /**
     * Retrieve a user with detailed profiles
     * @param Request $request
     * @param $uuid
     * @return object $json
     */
    public function getUser(Request $request, $uuid)
    {
        $myUuid = $this->guard()->getPayload()->get('sub');
        if ($myUuid !== $uuid) {
            /* Authenticate current user if it is not get my detail */
            $user = $this->guard()->user();
            if (!$user->hasRole(['administrator'])) {
                return response('Unauthorized', 401);
            }
        }

        $user = User::where('uuid', $uuid)->with(['role'])->first();

        return $this->response($user, 'get user error');
    }

    public function postUser(Request $request)
    {
        return $this->error('Unimplemented API');
    }

    public function putUser(Request $request, $uuid)
    {
        $body = json_decode($request->getContent(), true);

        /* This is a pivot record which is not going be stored directly */
        unset($body['role']);

        /* All relationships are striped, this is a bare user record */
        $user = $body;

        $newUser = null;
        if ($user && $user['id']) {
            unset($user['uuid'], $user['updated_at']);
            $newUser = User::where('id', $user['id'])->update($user)->toArray();
        }

        return $this->response($newUser, 'put user error');
    }

    public function deleteUser(Request $request, $uuid)
    {
        return $this->error('Unimplemented API');
    }
}
