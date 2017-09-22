<?php
/**
 * View of channel, used as relationship to post, topic etc
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttrChannelView extends Model
{
    protected $table = 'attr_channels_view';
    public $timestamps = false;
}