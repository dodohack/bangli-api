<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $table = 'newsletters';

    /*
     * Get a list of newsletter's revisions
     */
    public function revisions()
    {
        return $this->morphMany('App\Models\Revision', 'content');
    }
}
