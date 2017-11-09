<?php

namespace App\Models;

class FeTopic extends Topic
{
    // TODO: Can we remove this view?
    //protected $table = 'fe_view_topics';

    /*
     * Return an array of columns which are returned to client when request
     * multiple posts.
     */

    public function simpleColumns()
    {
        return ['topics.id', 'topics.channel_id',
            'location_id', 'ranking', 'guid', 'logo', 'display_url', 'tracking_url',
            'title', 'title_cn', 'topics.description', 'image_idx',
            'updated_at'];
    }

    /*
     * Return an array of columns which are returned to client when request
     * a single post.
     */

    public function fullColumns()
    {
        return ['topics.id', 'topics.channel_id',
            'location_id', 'ranking', 'guid', 'logo', 'display_url', 'tracking_url',
            'title', 'title_cn', 'topics.description', 'content',
            'image_idx', 'published_at', 'updated_at'];
    }

    public function simpleRelations()
    {
        return null;
    }

    public function fullRelations()
    {
        // All relations are needed by default
        return ['type', 'channel','offers', 'location', 'categories'];
    }

    /////////////////////////////////////////////////////////////////////////
    // Overwrite parent relationships, only query published relationship(with
    // query scope.

    public function posts()
    {
        return $this->belongsToMany('App\Models\Post',
            'topic_has_post', 'topic_id', 'post_id')
            ->publish();
    }

    public function topics()
    {
        return $this->belongsToMany('App\Models\Topic',
            'topic_has_topic', 'topic2_id', 'topic1_id')
            ->publish()
            ->select(['topics.id', 'channel_id', 'type_id', 'title']);
    }

    public function topics_reverse()
    {
        return $this->belongsToMany('App\Models\Topic',
            'topic_has_topic', 'topic1_id', 'topic2_id')
            ->publish()
            ->select(['topics.id', 'channel_id', 'type_id', 'title']);
    }

    public function offers()
    {
        return $this->belongsToMany('App\Models\Offer',
            'topic_has_offer', 'topic_id', 'offer_id')
            ->publish()->valid()
            ->select(['offers.id', 'title', 'tracking_url', 'vouchers',
                'featured', 'starts', 'ends']);
    }

    public function images()
    {
        return $this->belongsToMany('App\Models\Attachment',
            'topic_has_image', 'topic_id', 'image_id')
            ->select(['attachments.id', 'path', 'thumb_path', 'title',
                'filename', 'thumbnail']);
    }
}