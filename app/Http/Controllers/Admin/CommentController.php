<?php
/**
 * Dashboard comment controller
 * Shared by all domains.
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Controllers\EntityController;
use App\Models\Comment;

class CommentController extends EntityController
{
    /**
     * Return a list of posts
     * @param Request $request
     * @return object
     */
    public function getComments(Request $request)
    {
        return $this->getEntitiesReq($request);
    }

    /**
     * Update multiple comments
     */
    public function putComments(Request $request)
    {
        return response('Posts batch editing API unimplemented', 401);
    }

    /**
     * Move multiple comments into trash
     */
    public function deleteComments(Request $request)
    {
        return response('API unimplemented', 401);
    }

    /**
     * Return comment states and occurrences
     */
    public function getStates(Request $request)
    {
        return $this->getEntityStates($request, 'comments');
    }

    /**
     * Get a comment with it's relations
     * @param Request $request
     * @param $id - comment id
     * @return string
     */
    public function getComment(Request $request, $id)
    {
        return $this->getEntityReq($request, 'id', $id, null);
    }

    /**
     * Update comment by given id
     * @param Request $request
     * @param $id - comment id to be updated
     * @return object
     */
    public function putComment(Request $request, $id)
    {
        return $this->putEntityReq($request, 'id', $id);
    }

    /**
     * Create a new comment
     * @param Request $request
     * @return object
     */
    public function postComment(Request $request)
    {
        return $this->postEntityReq($request);
    }

    /**
     * Move a comment to trash by id
     * @param Request $request
     * @param $id
     * @return Comment
     */
    public function deleteComment(Request $request, $id)
    {
        return $this->deleteEntityReq($request, 'id', $id);
    }
}
