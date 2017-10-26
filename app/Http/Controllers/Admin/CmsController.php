<?php
/**
 * CMS controller, base class of all cms controllers
 * Shared by all domains.
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EntityController;
use App\Models\Category;
use App\Models\TopicType;
use App\Models\Channel;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Log;


class CmsController extends EntityController
{
    /**
     * Get a group of CMS related attributes to cache on client at app
     * start up. These cached data are only used as input select, do
     * not use with other purpose.
     */
    public function getAttributes(Request $request)
    {
        // Get list of available authors
        $authors = User::whereIn('role_id', [1,2,3,4])->get(['id', 'display_name'])->toArray();

        // Get list of available editors
        $editors = User::whereIn('role_id', [1,2,3])->get(['id', 'display_name'])->toArray();

        // Get all cms channels
        $channels = Channel::get(['id', 'slug', 'name'])->toArray();

        // Get all locations
        $locations = Location::get(['id', 'level', 'parent_id', 'name'])
            ->toArray();

        // Cms categories with number of posts/topics/deals per category
        $categories = Category::get(['id', 'channel_id', 'parent_id', 'name', 'slug'])
            ->toArray();

        // Cms topic types
        $topic_types = TopicType::get(['id', 'channel_id', 'name', 'slug'])->toArray();

        // FIXME: Hardcoded table names!!
        // Post status and occurrences
        $post_status = DB::table('posts')
            ->select(DB::raw('status, COUNT(*) as count'))
            ->groupBy('status')->get();


        // Topic statuss and occurrences
        $topic_status = DB::table('topics')
            ->select(DB::raw('status, COUNT(*) as count'))
            ->groupBy('status')->get();

        // Page statuss and occurrences
        $page_status = DB::table('pages')
            ->select(DB::raw('status, COUNT(*) as count'))
            ->groupBy('status')->get();

        // Deal status and occurrences
        $offer_status = DB::table('offers')
            ->select(DB::raw('status, COUNT(*) as count'))
            ->groupBy('status')->get();

        $json = compact('authors', 'editors', 'channels', 'locations',
            'categories', 'topic_types', 'post_status',
            'topic_status', 'offer_status', 'page_status');

        return Controller::success($request, $json);
    }
}