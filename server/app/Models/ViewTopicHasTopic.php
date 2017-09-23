<?php
/**
 * View of relationship between topic and topic.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewTopicHasTopic extends Model
{
    protected $table = 'view_topic_has_topic';
    protected $hidden = ['pivot'];
    public $timestamps = false;
}
