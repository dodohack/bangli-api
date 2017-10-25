<?php
/**
 * Relationship between topic and category.
 * This modal is only used by CmsCatController to update massive relationship
 * between topic and category.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicHasCategory extends Model
{
    protected $table = 'topic_has_category';
    protected $hidden = ['pivot'];
    public $timestamps = false;
}
