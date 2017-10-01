<?php
/**
 * Frontend view of topics with extra categories info joined
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeViewRelatedTopic extends Model
{
    protected $table = 'fe_view_related_topic';
    protected $hidden = ['pivot'];
    public $timestamps = false;
}
