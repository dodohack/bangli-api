<?php
/**
 * View of locations, used as relationship to post, topic, bbs thread etc
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewAttrLocation extends Model
{
    protected $table = 'view_attr_locations';
    public $timestamps = false;
}
