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

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Return a list of pages, no need to validate incoming parameters
     * cause this route is protected by middleware.
     */
    public function getPages(Request $request)
    {
        $pages = $this->getEntities($request->all(), $this->pagesRelations,
            null, $this->pagesColumns);

        return $this->response($pages, 'get pages error');
    }

    public function putPages(Request $request)
    {
        return $this->error('API unimplemented');
    }

    public function deletePages(Request $request)
    {
        $ids = $request->get('ids');
        $numDeleted = $this->deleteEntities($ids);

        return $this->response($numDeleted, 'trash pages error');
    }

    public function getStatus(Request $request)
    {
        $status = Page::select(DB::raw('status, COUNT(*) as count'))
            ->groupBy('status')->get();

        return $this->response(['status' => $status], 'get page status error');
    }

    public function getPage(Request $request, $id)
    {
        $page = $this->getEntity('id', $id, null, $this->pageRelations);
        return $this->response($page, 'get page error');
    }

    public function putPage(Request $request, $id)
    {
        $inputs = $request->all();

        $page = $this->putEntity($inputs, 'id', $id);

        return $this->response($page, 'put page error');
    }

    public function postPage(Request $request)
    {
        $inputs = $request->all();

        $page = $this->postEntity($inputs);

        return $this->response($page, 'post page error');
    }

    public function deletePage(Request $request, $id)
    {
        $deleted = $this->deleteEntity('id', $id);

        return $this->response($deleted, 'trash page error');
    }
}
