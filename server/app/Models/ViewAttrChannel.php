<?php
/**
 * View of channel, used as relationship to post, topic etc
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewAttrChannel extends Model
{
    protected $table = 'view_attr_channels';
    public $timestamps = false;
}