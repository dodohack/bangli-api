<?php
/**
 * Dashboard post controller
 * Shared by all domains.
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Models\Cms\Post;

class PostController extends CmsController
{
    /* Columns to be retrieved for posts list */
    private $postsColumns = ['cms_posts.id', 'editor_id', 'author_id', 'channel_id',
                          'location_id', 'lock', 'state', 'creative_type',
                          'title', 'excerpt', 'internal_note', 'fake_published_at',
                          'published_at', 'created_at', 'updated_at'];

    /* Relations to be queried with the post/posts */
    private $postsRelations = ['author', 'editor', 'channel', 'categories',
        'topics', 'statistics', 'activities'];
    private $postRelations = ['author', 'editor', 'channel', 'image',
        'categories', 'topics', 'revisions', 'statistics'];


    /**
     * Return a list of posts
     */
    public function getPosts(Request $request)
    {
        return $this->getEntitiesReq($request,
            $this->postsRelations, $this->postsColumns);
    }

    /**
     * Update multiple posts
     */
    public function putPosts(Request $request)
    {
        return response('Posts batch editing API unimplemented', 401);
    }

    /**
     * Move multiple posts into trash
     */
    public function deletePosts(Request $request)
    {
        return response('API unimplemented', 401);
    }

    /**
     * Return post states and occurrences
     */
    public function getStates(Request $request)
    {
        return $this->getEntityStates($request, 'cms_posts');
    }

    /**
     * Get a post with it's relations
     * @param Request $request
     * @param $id - post id
     * @return string
     */
    public function getPost(Request $request, $id)
    {
        return $this->getEntityReq($request,
            'id', $id, null, $this->postRelations);
    }

    /**
     * Update post by given id
     * @param Request $request
     * @param $id - post id to be updated
     * @return object
     */
    public function putPost(Request $request, $id)
    {
        return $this->putEntityReq($request, 'id', $id,
                                   $this->postRelations, null/* columns */);
    }

    /**
     * Create a new post
     * @param Request $request
     * @return object
     */
    public function postPost(Request $request)
    {
        return $this->postEntityReq($request);
    }

    /**
     * Move a post to trash by id
     * @param Request $request
     * @param $id
     * @return Post
     */
    public function deletePost(Request $request, $id)
    {
        return $this->deleteEntityReq($request, 'id', $id);
    }
}