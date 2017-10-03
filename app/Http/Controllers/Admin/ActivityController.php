<?php
/**
 * Activity controller, listen on dashboard users' ping w/wo data
 * Share by all domains.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Faker\Provider\zh_TW\DateTime;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

use App\Models\Cms\Post;
use App\Models\Cms\Topic;
use App\Models\Cms\Page;
use App\Models\Shop\Product;
use App\Models\Email\Newsletter;
use App\Models\Activity;
use App\Models\Statistic;

class ActivityController extends Controller
{
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * Process the beacon send from dashboard user, form parameters:
     * token: jwt token
     * action: 'ping', 'lock'
     * active: 'yes' or 'no', if the dashboard using in this domain, if 'yes',
     *         extra data will be returned to client.
     * type: optional, 'post', 'page', 'topic', 'product', 'newsletter' etc
     * ids: optional, content id of given type
     * @param Request $request
     * @return Object $json -  always return occupied resource type and id
     */
    public function handle(Request $request)
    {
        $action = $request->input('action', null);
        $active = $request->input('active', 'no');

        $user = $this->jwt->authenticate();

        // If user can still reach server with a 'ping' type, it means
        // all the locked content by the user can be released, otherwise
        // a 'lock' type is sent by user.
        switch($action) {
            case 'lock': {
                $type  = $request->input('type', null);
                $idstr = $request->input('ids', null);

                // Convert ',' seperated string into array
                $ids = explode(',', $idstr);

                // Invalid request
                if (!$idstr || count($ids) === 0)
                    return response('ERROR', 401);


                if (!$this->canUserLockContent($user, $ids))
                    return response('No permission', 401);

                if (!$this->lockContents($type, $ids, $user->id))
                    return response('ERROR', 401);

                break;
            }
            case 'ping': {
                // Try to unlock user locked content
                $this->unlockContent($user->id);
                break;
            }
        }

        if ($active === 'yes') {
            // Return list of locked resources, this is not too much
            $json = Activity::get(['id', 'content_type',
                'content_id', 'user_id', 'edit_lock', 'created_at'])
                ->groupBy('content_type')->toJson();
            return parent::success($request, $json);
        } else {
            // No data returns, this is the route client tests if api server
            // is online.
            return response('OK', 200);
        }
    }

    private function lockContents($type, $ids, $user_id)
    {
        $content_type = Activity::getContentType($type);
        if (!$content_type) return false;

        // lock given content
        if (is_array($ids)) {
            foreach ($ids as $id)
                $this->lockContent($content_type, $id, $user_id);
        } else {
            $this->lockContent($content_type, $ids, $user_id);
        }
        return true;
    }

    private function lockContent($content_type, $id, $user_id)
    {
        $post = Post::find($id);
        $activity = $post->activities()->first();

        if (is_null($activity)) // Create new record
            $post->activities()->create([
                'edit_lock'    => 1,
                'user_id'      => $user_id]);
        else // Update updated_at
            $post->activities()->touch();
    }

    // Remove all entries haven't locked by the user for 2 mins
    private function unlockContent($user_id)
    {
        Activity::where('user_id', $user_id)
            ->where('updated_at', '<', date('Y-m-d H:i:s', time() - 120))
            ->delete();
    }

    private function canUserLockContent($user, $ids)
    {
        if ($user->hasRole(['author', 'editor', 'shop_manager', 'administrator']))
            return true;

        return false;
    }
}
