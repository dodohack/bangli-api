<?php
/**
 * View of topic type model
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewAttrTopicType extends Model
{
    protected $table = 'view_attr_topic_types';
    protected $hidden = ['pivot'];
    public $timestamps = false;
}
