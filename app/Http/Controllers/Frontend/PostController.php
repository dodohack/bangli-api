<?php
/**
 * Frontend post controller
 */

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;

use App\Models\FePost;
use App\Models\ViewTopicHasPost;

class PostController extends FeController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Get a list of published posts
     * @param Request $request
     * @return mixed a list of published posts
     */
    public function getPosts(Request $request)
    {
        $inputs = $request->all();
        $relations = null;

        if (isset($inputs['relations']))
            $relations = $this->getRelations($inputs['relations']);

        $posts = $this->getEntitiesByKey($inputs, $relations,
            null, null, 'full');

        return $this->response($posts, 'get posts error');
    }

    /**
     * Get single/multiple group of listed published posts with filter key
     * as index of each returned group.
     * @param Request $request
     * @return mixed
     */
    public function getGroupPosts(Request $request)
    {
        $postGroups = $this->getGroupedEntities($request->all(),
            null, null);

        return $this->success($postGroups);
    }

    /**
     * Get a single post
     * @param Request $request
     * @param $id  - post id
     * @return mixed
     */
    public function getPost(Request $request, $id)
    {
        $post = $this->getEntity('id', $id);

        return $this->response($post, 'post not found');
    }

}
