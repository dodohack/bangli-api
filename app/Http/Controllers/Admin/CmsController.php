<?php
/**
 * CMS controller, base class of all cms controllers
 * Shared by all domains.
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\EntityController;
use App\Models\Cms\AttrCategoryView;
use App\Models\Cms\AttrTopicTypeView;
use App\Models\Cms\AttrChannelView;
use App\Models\AttrLocationView;
use App\Models\EditorView;
use App\Models\AuthorView;
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
        $authors = AuthorView::get()->toArray();

        // Get list of available editors
        $editors = EditorView::get()->toArray();

        // Get all cms channels
        $channels = AttrChannelView::get()->toArray();

        // Get all locations
        $locations = AttrLocationView::get()->toArray();

        // Cms categories with number of posts/topics/deals per category
        $categories = AttrCategoryView::get()->toArray();

        // Cms topic types
        $topic_types = AttrTopicTypeView::get()->toArray();

        // Post states and occurrences
        $post_states = DB::table('cms_posts')
            ->select(DB::raw('state, COUNT(*) as count'))
            ->groupBy('state')->get();

        // Post creative_type and occurrences
        /*
        $post_creative_types = DB::table('cms_posts')
            ->select(DB::raw('creative_type, COUNT(*) as count'))
            ->groupBy('creative_type')->get();
        */

        // Topic states and occurrences
        $topic_states = DB::table('cms_topics')
            ->select(DB::raw('state, COUNT(*) as count'))
            ->groupBy('state')->get();

        // Page states and occurrences
        $page_states = DB::table('cms_pages')
            ->select(DB::raw('state, COUNT(*) as count'))
            ->groupBy('state')->get();

        // Deal states and occurrences
//        $deal_states = DB::table('cms_deals')
//            ->select(DB::raw('state, COUNT(*) as count'))
//            ->groupBy('state')->get();

        $json = compact('authors', 'editors', 'channels', 'locations',
            'categories', 'topic_types', 'post_states',
            'topic_states', 'page_states');

        return parent::success($request, $json);
    }
}