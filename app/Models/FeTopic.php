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
            'location_id', 'ranking', 'guid', 'display_url', 'tracking_url',
            'title', 'title_cn', 'topics.description', 'content', 'published_at',
            'created_at', 'updated_at'];
    }

    /*
     * Return an array of columns which are returned to client when request
     * a single post.
     */

    public function fullColumns()
    {
        return ['topics.id', 'topics.channel_id',
            'location_id', 'ranking', 'guid', 'display_url', 'tracking_url',
            'title', 'title_cn', 'topics.description', 'content', 'published_at',
            'updated_at'];
    }

    public function simpleRelations()
    {
        return null;
    }

    public function fullRelations()
    {
        // All relations are needed by default
        return ['type', 'channel', 'location', 'categories'];
    }

    /**
     * Columns return to client for single topic page
     */
    static public function topic_columns()
    {
        return ['id', 'type_id', 'channel_id', 'location_id', 'guid', 'ranking',
            'logo', 'display_url', 'tracking_url', 'title', 'title_cn',
            'description', 'content', 'updated_at'];
    }

    /**
     * Columns return to client for list of topics
     */
    static public function topics_columns()
    {
        return ['topics.id', 'guid', 'title'];
    }

    /**
     * Relations return to client with single topic page
     */
    static public function topic_relations()
    {
        return ['type', 'channel', 'offers', 'location', 'categories'];
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
            ->select(['topics.id', 'channel_id', 'type_id', 'title'])
            ->publish();
    }

    public function topics_reverse()
    {
        return $this->belongsToMany('App\Models\Topic',
            'topic_has_topic', 'topic1_id', 'topic2_id')
            ->select(['topics.id', 'channel_id', 'type_id', 'title'])
            ->publish();
    }

    public function offers()
    {
        return $this->belongsToMany('App\Models\Offer',
            'topic_has_offer', 'topic_id', 'offer_id')
            ->publish();
    }

}