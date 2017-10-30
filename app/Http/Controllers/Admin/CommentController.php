<?php
/**
 * Dashboard comment controller
 * Shared by all domains.
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\EntityController;
use App\Models\Comment;

class CommentController extends EntityController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Return a list of posts
     * @param Request $request
     * @return object
     */
    public function getComments(Request $request)
    {
        return $this->error('API unimplemented');
    }

    /**
     * Update multiple comments
     */
    public function putComments(Request $request)
    {
        return $this->error('API unimplemented');
    }

    /**
     * Move multiple comments into trash
     */
    public function deleteComments(Request $request)
    {
        return $this->error('API unimplemented');
    }

    /**
     * Return comment status and occurrences
     */
    public function getStatus(Request $request)
    {
        return $this->error('API unimplemented');
    }

    /**
     * Get a comment with it's relations
     * @param Request $request
     * @param $id - comment id
     * @return string
     */
    public function getComment(Request $request, $id)
    {
        return $this->error('API unimplemented');
    }

    /**
     * Update comment by given id
     * @param Request $request
     * @param $id - comment id to be updated
     * @return object
     */
    public function putComment(Request $request, $id)
    {
        return $this->error('API unimplemented');
    }

    /**
     * Create a new comment
     * @param Request $request
     * @return object
     */
    public function postComment(Request $request)
    {
        return $this->error('API unimplemented');
    }

    /**
     * Move a comment to trash by id
     * @param Request $request
     * @param $id
     * @return Comment
     */
    public function deleteComment(Request $request, $id)
    {
        return $this->error('API unimplemented');
    }
}
