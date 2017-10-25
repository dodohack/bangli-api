<?php
/**
 * Relationship between post and category.
 * This modal is only used by CmsCatController to update massive relationship
 * between post and category.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostHasCategory extends Model
{
    protected $table = 'post_has_category';
    protected $hidden = ['pivot'];
    public $timestamps = false;
}
