<?php
/**
 * Dashboard cms page controller
 * Shared by all domains.
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Page;


class PageController extends CmsController
{

    // FIXME
    /* Columns to be retrieved for pages list */
    private $pagesColumns = ['pages.id', 'editor_id', 'lock', 'status', 'page_type',
        'title', 'created_at', 'updated_at'];

    /* Relations to be queried with page/pages */
    private $pagesRelations = ['statistics', 'activities'];
    private $pageRelations = ['revisions', 'statistics'];

    /**
     * Return a list of pages, no need to validate incoming parameters
     * cause this route is protected by middleware.
     */
    public function getPages(Request $request)
    {
        return $this->getEntitiesReq($request,
            $this->pagesRelations, $this->pagesColumns);
    }

    public function putPages(Request $request)
    {
        return response('unimplemented API', 401);
    }

    public function deletePages(Request $request)
    {
        return response('unimplemented API', 401);
    }

    /**
     * Return page statuss
     *
     * @param Request $request
     * @return object $json: jsonified pagination
     */
    public function getStates(Request $request)
    {
        // FIXME
        return $this->getEntityStates($request, 'pages');
    }

    /**
     * Get a page
     * @param Request $request
     * @param $id
     * @return object
     */
    public function getPage(Request $request, $id)
    {
        return $this->getEntityReq($request, 'id', $id,
            null, $this->pageRelations);
    }

    /**
     * Update page by given guid
     * @param Request $request
     * @param $id - page id to be updated
     * @return object
     */
    public function putPage(Request $request, $id)
    {
        return $this->putEntityReq($request, 'id', $id);
    }

    /**
     * Create a new page
     * @param Request $request
     * @return object
     */
    public function postPage(Request $request)
    {
        return $this->postEntityReq($request);
    }

    /**
     * Move a page to trash by uuid
     * @param Request $request
     * @param $id
     * @return Page
     */
    public function deletePage(Request $request, $id)
    {
        return $this->deleteEntityReq($request, 'id', $id);
    }
}
