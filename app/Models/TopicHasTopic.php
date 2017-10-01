<?php
/**
 * Relationship between topic and topic.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicHasTopic extends Model
{
    protected $table = 'topic_has_topic';
    protected $hidden = ['pivot'];
    public $timestamps = false;
}
