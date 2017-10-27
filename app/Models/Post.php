<?php
namespace App\Models;

class Post extends EntityModel
{
    protected $table = 'posts';
    // Make all attributes mass assignable.
    protected $fillable = ['id', 'editor_id', 'author_id', 'image_id',
        'channel_id', 'location_id', 'lock', 'featured', 'status',
        'title', 'excerpt', 'content', 'extra', 'published_at'];
    protected $hidden = ['pivot'];

    /*
     * Return an array of columns which are returned to client when request
     * multiple posts.
     */
    public function simpleColumns()
    {
        return ['post.id', 'editor_id', 'author_id',
            'channel_id', 'location_id', 'lock', 'featured', 'status',
            'title', 'published_at', 'created_at', 'updated_at'];
    }

    /*
     * Return an array of columns which are returned to client when request
     * a single post.
     */
    public function fullColumns()
    {
        // All columns are needed by default
        return null;
    }

    public function simpleRelations()
    {
        return ['author', 'editor', 'channel', 'categories',
        'topics', 'statistics', 'activities'];
    }

    public function fullRelations()
    {
        return ['author', 'editor', 'channel', 'image',
            'categories', 'topics', 'revisions', 'statistics'];
    }

    ////////////////////////////////////////////////////////////////////////
    // Relations

    /*
     * Get the author that owns the post
     */
    public function author()
    {
        return $this->belongsTo('App\Models\User', 'author_id')
            ->select(['id', 'display_name']);
    }

    /*
     * Get the editor that owns the post
     */
    public function editor()
    {
        return $this->belongsTo('App\Models\User', 'editor_id')
            ->select(['id', 'display_name']);
    }

    /*
     * The channel this post belongs to
     */
    public function channel()
    {
        return $this->belongsTo('App\Models\Channel', 'channel_id')
            ->select(['channels.id', 'slug', 'name']);
    }

    /*
     * The location this post belongs to
     */
    public function location()
    {
        return $this->belongsTo('App\Models\Location', 'location_id')
            ->select(['locations.id', 'level', 'parent_id', 'name', 'slug']);
    }

    /*
     * Feature image of the post
     */
    public function image()
    {
        return $this->belongsTo('App\Models\Attachment', 'image_id');
    }

    /*
     * Get the categories of this post
     */
    public function categories()
    {
        return $this->belongsToMany('App\Models\Category',
            'post_has_category', 'post_id', 'cat_id')
            ->select(['categories.id', 'channel_id', 'parent_id', 'name']);
    }

    /*
     * Get the topics this post belongs to
     */
    public function topics()
    {
        return $this->belongsToMany('App\Models\Topic',
            'topic_has_post', 'post_id', 'topic_id')
            ->select(['topics.id', 'channel_id', 'type_id', 'title']);
    }

    /*
     * Get a list of post's revisions
     */
    public function revisions()
    {
        return $this->morphMany('App\Models\Revision', 'content');
    }

    /*
     * Get post's statistic
     */
    public function statistics()
    {
        return $this->morphMany('App\Models\Statistic', 'content');
    }

    /*
     * Get post's activity
     */
    public function activities()
    {
        return $this->morphMany('App\Models\Activity', 'content');
    }

    /*
     * Get post's comments
     */
    public function comments()
    {
        return $this->morphMany('App\Models\Comment', 'commentable');
    }

    /**
     * Query scope: featured posts
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /*
     * query scope
     * A post in publish status
     */
    public function scopePublish($query)
    {
        return $query->where('status', 'publish');
    }
}
