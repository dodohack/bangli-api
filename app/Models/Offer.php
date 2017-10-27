<?php

namespace App\Models;

class Offer extends EntityModel
{
    protected $table    = 'offers';
    protected $hidden   = ['pivot'];
    protected $fillable = ['id', 'author_id', 'channel_id', 'status', 'featured',
        'title', 'tracking_url', 'display_url', 'vouchers',
        'aff_offer_id', 'starts', 'ends',
        'created_at', 'updated_at', 'published_at'];


    public function simpleColumns()
    {
        return null;
    }

    public function fullColumns()
    {
        return null;
    }

    public function simpleRelations()
    {
        return ['topics'];
    }

    public function fullRelations()
    {
        return ['topics'];
    }

    public function editor()
    {
        return $this->belongsTo('App\Models\User', 'editor_id')
            ->select(['id', 'display_name']);
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
        return $this->belongsToMany('App\Models\Category',
            'offer_has_category', 'offer_id', 'cat_id');
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
     * Query scope: featured offers
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
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

    /**
     * Query scope: offer is started and is not expired
     * @param $query
     */
    public function scopeValid($query)
    {
        $now = date('Y-m-d H:i:s');
        return $query->where('starts', '<=', $now)->where('ends', '>=', $now);
    }
}