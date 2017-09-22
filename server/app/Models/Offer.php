<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $table    = 'offers';
    protected $hidden   = ['pivot'];
    protected $fillable = ['id', 'merchant_id', 'author_id', 'status', 'type',
        'exclusive', 'code', 'description', 'starts', 'ends', 'region_id',
        'tracking_link', 'deeplink', 'modified_at', 'published_at', 'featured'];

    public function editor()
    {
        return $this->belongsTo('App\Models\User', 'editor_id');
    }

    /**
     * The channel this offer belongs to
     */
    public function channel()
    {
        return $this->belongsTo('App\Models\Channel', 'channel_id');
    }

    public function categories()
    {
        // TODO: Do we need to support this?
    }

    /**
     * Get the topics this offer belongs to
     */
    public function topics()
    {
        return $this->belongsToMany('App\Models\Topic',
            'topic_has_offer', 'offer_id', 'topic_id');
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