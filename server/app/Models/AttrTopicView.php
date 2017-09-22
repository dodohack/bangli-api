<?php
/**
 * View of topics, used as relationship to post, topic etc
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttrTopicView extends Model
{
    protected $table = 'attr_topics_view';
    /* Do not return these field when not querying it explicitly */
    protected $hidden = ['pivot'];
    public $timestamps = false;
}
