<?php
/**
 * CMS controller, base class of all cms controllers
 * Shared by all domains.
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\EntityController;
use App\Models\ViewAttrCategory;
use App\Models\ViewAttrTopicType;
use App\Models\ViewAttrChannel;
use App\Models\ViewAttrLocation;
use App\Models\ViewEditor;
use App\Models\ViewAuthor;
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
        $authors = ViewAuthor::get()->toArray();

        // Get list of available editors
        $editors = ViewEditor::get()->toArray();

        // Get all cms channels
        $channels = ViewAttrChannel::get()->toArray();

        // Get all locations
        $locations = ViewAttrLocation::get()->toArray();

        // Cms categories with number of posts/topics/deals per category
        $categories = ViewAttrCategory::get()->toArray();

        // Cms topic types
        $topic_types = ViewAttrTopicType::get()->toArray();

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
//        $deal_status = DB::table('offers')
//            ->select(DB::raw('status, COUNT(*) as count'))
//            ->groupBy('status')->get();

        $json = compact('authors', 'editors', 'channels', 'locations',
            'categories', 'topic_types', 'post_status',
            'topic_status', 'page_status');

        return parent::success($request, $json);
    }
}