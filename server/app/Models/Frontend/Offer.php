<?php
/**
 *
 */

namespace App\Models\Frontend;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $table    = 'offers';
    protected $hidden   = ['pivot'];
    protected $fillable = ['id', 'merchant_id', 'author_id', 'status', 'type',
        'exclusive', 'code', 'description', 'starts', 'ends', 'region_id',
        'tracking_link', 'deeplink', 'modified_at', 'published_at', 'featured'];

    /**
     * Get the region the offer applicable
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function region()
    {
        return $this->belongsTo('App\Models\Region', 'region_id');
    }

    /**
     * Query scope: get published offers
     * @param $query
     * @return mixed
     */
    public function scopePublish($query)
    {
        return $query->where('status', 'publish');
    }
}