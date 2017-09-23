<?php
/**
 * View of relationship between topic and post.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewTopicHasPost extends Model
{
    protected $table = 'view_topic_has_post';
    protected $hidden = ['pivot'];
    public $timestamps = false;
}
