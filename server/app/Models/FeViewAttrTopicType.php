<?php
/**
 * Frontend view of topic type model
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeViewAttrTopicType extends Model
{
    protected $table = 'fe_view_attr_topic_types';
    protected $hidden = ['pivot'];
    public $timestamps = false;
}
