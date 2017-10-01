<?php
/**
 * Frontend view of locations, used as relationship to post, topic, bbs thread etc
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeViewAttrLocation extends Model
{
    protected $table = 'fe_view_attr_locations';
    public $timestamps = false;
}
