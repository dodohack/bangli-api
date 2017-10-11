<?php
/**
 * Frontend post view
 */

namespace App\Models;

class FePost extends Post
{
    protected $table = 'fe_view_posts';

    /*
     * Return an array of columns which are returned to client when request
     * multiple posts.
     */
    public function simpleColumns()
    {
        // NOTE: Table name on id must be given, used by explicit SQL join
        return ['fe_view_posts.id', 'author_id', 'image_id', 'channel_id',
            'title', 'updated_at'];
    }

    /*
     * Return an array of columns which are returned to client when request
     * a single post.
     */
    public function fullColumns()
    {
        // NOTE: Table name on id must be given, used by explicit SQL join
        return ['fe_view_posts.id', 'author_id', 'image_id', 'channel_id',
            'title', 'content', 'updated_at'];
    }

    /*
     * Simple relationship for frontend post
     */
    public function simpleRelations()
    {
        return ['categories'];
    }

    /*
     * Full relationship for frontend post
     */
    public function fullRelations()
    {
        return ['author', 'categories'];
    }

    /////////////////////////////////////////////////////////////////////////
    // Overwrite parent relationships,  only a few columns should be
    // retrieved with.

    /*
 * Get the topics this post belongs to
 */
    public function topics()
    {
        return $this->belongsToMany('App\Models\Topic',
            'topic_has_post', 'post_id', 'topic_id')
            ->select(['topics.id', 'channel_id', 'type_id', 'title'])
            ->publish();
    }
}
