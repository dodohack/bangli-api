<?php
/**
 * Advertisement model
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advertise extends Model
{
    protected $table = 'advertises';
    protected $hidden = ['pivot'];
    protected $fillable = ['id', 'state', 'device', 'position', 'location',
        'image_id', 'title', 'description', 'target_url', 'started_at',
        'ended_at'];

    /*
     * Get the editor that owns the deal
     */
    public function editor()
    {
        return $this->belongsTo('App\Models\User', 'editor_id');
    }

    /*
     * Get deal's statistic
     */
    public function statistics()
    {
        return $this->morphMany('App\Models\Statistic', 'content');
    }

    /*
     * query scope
     * A deal in publish status
     */
    public function scopePublish($query)
    {
        return $query->where('status', 'publish');
    }

}