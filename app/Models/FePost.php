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
     * The channel this topic belongs to
     */
    public function channel()
    {
        return $this->belongsTo('App\Models\Channel', 'channel_id')
            ->select(['channel.id', 'slug', 'name']);
    }

    /*
     * The topic type this topic belongs to
     */
    public function type()
    {
        return $this->belongsTo('App\Models\TopicType', 'type_id')
            ->select(['topic_types.id', 'slug', 'name']);
    }

    /*
     * The location this topic belongs to
     */
    public function location()
    {
        return $this->belongsTo('App\Models\Location', 'location_id')
            ->select(['locations.id', 'level', 'parent_id', 'name', 'slug']);
    }
}
