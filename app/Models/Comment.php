<?php

namespace App\Models;

class Comment extends EntityModel
{
    protected $table = 'comments';

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

    /**
     * Get the cms post/topic which owns the comments
     */
    public function commentable()
    {
        return $this->morphTo();
    }
}
