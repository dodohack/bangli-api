<?php
/**
 * View of topic type model
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicType extends Model
{
    protected $table = 'topic_types';
    protected $hidden = ['pivot'];
    public $timestamps = false;
}
