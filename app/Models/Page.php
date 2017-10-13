<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $table = 'pages';
    // Make all attributes mass assignable.
    protected $fillable = ['id', 'editor_id', 'lock', 'status', 'page_type',
        'title', 'content'];
    protected $hidden = ['pivot'];


    /*
     * Return an array of columns which are returned to client when request
     * multiple posts.
     */
    public function simpleColumns()
    {
        return null;
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

    /*
     * Get the editor that owns the page
     */
    public function editor()
    {
        return $this->belongsTo('App\Models\User', 'editor_id')
            ->select(['id', 'display_name']);
    }

    /*
     * Get a list of page's revisions
     */
    public function revisions()
    {
        return $this->morphMany('App\Models\Revision', 'content');
    }

    /*
     * Get page's statistic
     */
    public function statistics()
    {
        return $this->morphMany('App\Models\Statistic', 'content');
    }

    /*
     * Get page's activity
     */
    public function activities()
    {
        return $this->morphMany('App\Models\Activity', 'content');
    }

    /*
     * query scope
     * A page in publish status
     */
    public function scopePublish($query)
    {
        return $query->where('status', 'publish');
    }

}