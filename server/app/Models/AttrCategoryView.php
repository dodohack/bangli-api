<?php
/**
 * View of cms_categories, used as relationship to post, topic etc
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttrCategoryView extends Model
{
    protected $table = 'attr_categories_view';
    /* Do not return these field when not querying it explicitly */
    protected $hidden = ['pivot'];
    public $timestamps = false;
}
