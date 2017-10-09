<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $table = 'topics';
    // Make all attributes mass assignable
    protected $fillable = ['id', 'author_id', 'editor_id', 'channel_id',
        'type_id', 'location_id', 'lock', 'logo', 'ranking', 'featured',
        'status', 'guid', 'aff_id', 'aff_platform', 'display_url',
        'tracking_url', 'title', 'title_cn', 'description', 'content',
        'published_at'];

    protected $hidden = ['pivot'];

    public function editor()
    {
        return $this->belongsTo('App\Models\ViewEditor', 'editor_id');
    }

    public function channel()
    {
        return $this->belongsTo('App\Models\Channel', 'channel_id')
            ->select(['channels.id', 'slug', 'name']);
    }

    public function type()
    {
        return $this->belongsTo('App\Models\TopicType', 'type_id')
            ->select(['topic_types.id', 'channel_id', 'name']);
    }

    public function location()
    {
        return $this->belongsTo('App\Models\Location', 'location_id')
            ->select(['locations.id', 'level', 'parent_id', 'name']);
    }

    public function images()
    {
        return $this->belongsToMany('App\Models\Attachment',
            'topic_has_image', 'topic_id', 'image_id');
    }

    public function categories()
    {
        return $this->belongsToMany('App\Models\Category',
            'topic_has_category', 'topic_id', 'cat_id')
            ->select(['categories.id', 'channel_id', 'parent_id', 'name']);
    }

    public function posts()
    {
        return $this->belongsToMany('App\Models\Post',
            'topic_has_post', 'topic_id', 'post_id');
    }

    public function topics()
    {
        return $this->belongsToMany('App\Models\Topic',
            'topic_has_topic', 'topic2_id', 'topic1_id')
            ->select(['topics.id', 'channel_id', 'type_id', 'title']);
    }

    public function topics_reverse()
    {
        return $this->belongsToMany('App\Models\Topic',
            'topic_has_topic', 'topic1_id', 'topic2_id')
            ->select(['topics.id', 'channel_id', 'type_id', 'title']);
    }

    public function offers()
    {
        return $this->belongsToMany('App\Models\Offer',
            'topic_has_offer', 'topic_id', 'offer_id');
    }

    public function revisions()
    {
        return $this->morphMany('App\Models\Revision', 'content');
    }

    public function statistics()
    {
        return $this->morphMany('App\Models\Statistic', 'content');
    }

    public function activities()
    {
        return $this->morphMany('App\Models\Activity', 'content');
    }

    public function comments()
    {
        return $this->morphMany('App\models\Comment', 'commentable');
    }

    /* Query scope: a topic in publish status */
    public function scopePublic($query)
    {
        return $query->where('status', 'publish');
    }
}
