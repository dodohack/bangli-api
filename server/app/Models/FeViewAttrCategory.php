<?php
/**
 * Frontend view of categories, used as relationship to post, topic etc
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeViewAttrCategory extends Model
{
    protected $table = 'fe_view_attr_categories';
    protected $hidden = ['pivot'];
    public $timestamps = false;
}
