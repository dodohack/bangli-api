<?php
/**
 * Dashboard post controller
 * Shared by all domains.
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\EntityController;

use App\Models\Post;

class PostController extends EntityController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Return a list of posts
     */
    public function getPosts(Request $request)
    {
        $posts = $this->getEntities($request->all());

        return $this->response($posts, 'get posts error');
    }

    /**
     * Update multiple posts
     */
    public function putPosts(Request $request)
    {
        return $this->error('API unimplemented');
    }

    /**
     * Move multiple posts into trash or physically delete them from trash
     */
    public function deletePosts(Request $request)
    {
        $ids = $request->get('ids');
        $numDeleted = $this->deleteEntities($ids);

        return $this->response($numDeleted, 'trash posts error');
    }

    /**
     * Return post status and occurrences
     */
    public function getStatus(Request $request)
    {
        $status = Post::select(DB::raw('status, COUNT(*) as count'))
            ->groupBy('status')->get();

        return $this->response(['status' => $status], 'get post status error');
    }

    /**
     * Get a post
     * @param Request $request
     * @param $id - post id
     * @return string
     */
    public function getPost(Request $request, $id)
    {
        $post = $this->getEntity('id', $id);

        return $this->response($post, 'get post error');
    }

    /**
     * Update post by given id
     * @param Request $request
     * @param $id - post id to be updated
     * @return object
     */
    public function putPost(Request $request, $id)
    {
        $inputs = $request->all();

        $post = $this->putEntity($inputs, 'id', $id);

        return $this->response($post, 'put post error');
    }

    /**
     * Create a new post
     * @param Request $request
     * @return object
     */
    public function postPost(Request $request)
    {
        $inputs = $request->all();

        $post = $this->postEntity($inputs);

        return $this->response($post, 'post post error');
    }

    /**
     * Move a post to trash or physically delete it from trash
     * @param Request $request
     * @param $id
     * @return Post | bool
     */
    public function deletePost(Request $request, $id)
    {
        $deleted = $this->deleteEntity('id', $id);

        return $this->response($deleted, 'trash post error');
    }
}
