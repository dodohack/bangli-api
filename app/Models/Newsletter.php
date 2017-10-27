<?php

namespace App\Models;

class Newsletter extends EntityModel
{
    protected $table = 'newsletters';

    public function simpleColumns()
    {
        return null;
    }

    public function fullColumns()
    {
        return null;
    }

    public function simpleRelations()
    {
        return null;
    }

    public function fullRelations()
    {
        return null;
    }

    /*
     * Get a list of newsletter's revisions
     */
    public function revisions()
    {
        return $this->morphMany('App\Models\Revision', 'content');
    }
}
