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
        'tracking_url', 'title', 'title_cn', 'description', 'content', 'published_at'];

    protected $hidden = ['pivot'];

    public function editor()
    {
        return $this->belongsTo('App\Models\ViewEditor', 'editor_id');
    }

    public function channel()
    {
        return $this->belongsTo('App\Models\ViewAttrChannel', 'channel_id');
    }

    public function type()
    {
        return $this->belongsTo('App\Models\ViewAttrTopicType', 'type_id');
    }

    public function location()
    {
        return $this->belongsTo('App\Models\ViewAttrLocation', 'location_id');
    }

    public function images()
    {
        return $this->belongsToMany('App\Models\Attachment',
            'topic_has_image', 'topic_id', 'image_id');
    }

    public function categories()
    {
        return $this->belongsToMany('App\Models\ViewAttrCategory',
            'topic_has_category', 'topic_id', 'cat_id');
    }

    public function posts()
    {
        return $this->belongsToMany('App\Models\Post',
            'topic_has_post', 'topic_id', 'post_id');
    }

    public function topics()
    {
        return $this->belongsToMany('App\Models\ViewAttrTopic',
            'topic_has_topic', 'topic2_id', 'topic1_id');
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
