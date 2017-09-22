<?php
/**
 * View of Cms topic type model
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttrTopicTypeView extends Model
{
    protected $table = 'attr_topic_types_view';
    protected $hidden = ['pivot'];
    public $timestamps = false;
}
