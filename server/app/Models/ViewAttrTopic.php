<?php
/**
 * View of topics, used as relationship to post, topic etc
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewAttrTopic extends Model
{
    protected $table = 'view_attr_topics';
    /* Do not return these field when not querying it explicitly */
    protected $hidden = ['pivot'];
    public $timestamps = false;
}
