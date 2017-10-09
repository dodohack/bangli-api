<?php

namespace App\Models;

class FeTopic extends Topic
{
    protected $table = 'fe_view_topics';

    /*
     * Return an array of columns which are returned to client when request
     * multiple posts.
     */
    public function simpleColumns()
    {
        return ['fe_view_topics.id', 'fe_view_topics.channel_id',
            'location_id', 'ranking', 'guid', 'display_url', 'tracking_url',
            'title', 'title_cn', 'description', 'content', 'published_at',
            'created_at', 'updated_at'];
    }

    /*
     * Return an array of columns which are returned to client when request
     * a single post.
     */
    public function fullColumns()
    {
        return ['fe_view_topics.id', 'fe_view_topics.channel_id',
            'location_id', 'ranking', 'guid', 'display_url', 'tracking_url',
            'title', 'title_cn', 'description', 'content', 'published_at',
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
    // Overwrite parent relationships, only a few columns should be
    // retrieved with.

    /*
     * The channel this topic belongs to
     */
    public function channel()
    {
        return $this->belongsTo('App\Models\Channel', 'channel_id')
            ->select(['channel.id', 'slug', 'name']);
    }

    /*
     * The topic type this topic belongs to
     */
    public function type()
    {
        return $this->belongsTo('App\Models\TopicType', 'type_id')
            ->select(['topic_types.id', 'slug', 'name']);
    }

    /*
     * The location this topic belongs to
     */
    public function location()
    {
        return $this->belongsTo('App\Models\Location', 'location_id')
            ->select(['locations.id', 'level', 'parent_id', 'name', 'slug']);
    }

    /*
     * Relationship between topic and category
     */
    public function categories()
    {
        return $this->belongsToMany('App\Models\Category',
            'topic_has_category', 'topic_id', 'cat_id')
            ->select(['categories.id', 'parent_id', 'name', 'slug']);
    }
}