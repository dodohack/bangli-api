<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'post';
    // Make all attributes mass assignable.
    protected $fillable = ['id', 'editor_id', 'author_id', 'image_id',
        'channel_id', 'location_id', 'lock', 'status',
        'title', 'excerpt', 'content', 'extra',
        'published_at'];
    protected $hidden = ['pivot'];

    /*
     * Return an array of columns which are returned to client when request
     * multiple posts.
     */
    public function simpleColumns()
    {
        return ['post.id', 'editor_id', 'author_id', 'image_id',
            'channel_id', 'location_id', 'lock', 'status',
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
        // All relations are needed by default
        return null;
    }

    public function fullRelations()
    {
        // All relations are needed by default
        return null;
    }

    ////////////////////////////////////////////////////////////////////////
    // Relations

    /*
     * Get the author that owns the post
     */
    public function author()
    {
        return $this->belongsTo('App\Models\AuthorView', 'author_id');
    }

    /*
     * Get the editor that owns the post
     */
    public function editor()
    {
        return $this->belongsTo('App\Models\EditorView', 'editor_id');
    }

    /*
     * The channel this post belongs to
     */
    public function channel()
    {
        return $this->belongsTo('App\Models\AttrChannelView', 'channel_id');
    }

    /*
     * The location this post belongs to
     */
    public function location()
    {
        return $this->belongsTo('App\Models\AttrLocationView', 'location_id');
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
        return $this->belongsToMany('App\Models\AttrCategoryView',
            'post_has_category', 'post_id', 'cat_id');
    }

    /*
     * Get the topics this post belongs to
     */
    public function topics()
    {
        return $this->belongsToMany('App\Models\AttrTopicView',
            'topic_has_post', 'post_id', 'topic_id');
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

    /*
     * query scope
     * A post in publish status
     */
    public function scopePublish($query)
    {
        return $query->where('status', 'publish');
    }
}
