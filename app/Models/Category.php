<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $hidden = ['pivot'];
    protected $fillable = ['parent_id', 'channel_id', 'slug', 'name', 'description'];
    public $timestamps = false;

    /*
     * Get the posts belongs to this category
     */
    public function posts()
    {
        return $this->belongsToMany('App\Models\Post',
            'post_has_category', 'cat_id', 'post_id');
    }

    /*
     * Relationship between category and topic
     */
    public function topics()
    {
        return $this->belongsToMany('App\Models\Topic',
            'topic_has_category', 'cat_id', 'topic_id');
    }

    /*
     * Relationship between category and offers
     */
    public function offers()
    {
        return $this->belongsToMany('App\Models\Offer',
            'offer_has_category', 'cat_id', 'offer_id');
    }
}