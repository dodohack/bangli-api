<?php
/**
 * Advertisement model
 */

namespace App\Models;

class Advertise extends EntityModel
{
    protected $table = 'advertises';
    protected $hidden = ['pivot'];
    protected $fillable = ['id', 'channel_id', 'status', 'position', 'location',
        'rank', 'title', 'description', 'image_url', 'target_url', 'starts', 'ends'];

    public function simpleColumns()
    {
        return ['advertises.id', 'channel_id', 'status', 'position', 'location',
            'rank', 'title', 'image_url', 'target_url', 'starts', 'ends'];
    }

    public function fullColumns()
    {
        // All columns are needed by default
        return null;
    }

    public function simpleRelations()
    {
        // No relationship by default
        return null;
    }

    public function fullRelations()
    {
        // No relationship by default
        return null;
    }


    //////////////////////////////////////////////////////////////////////////
    // Relations

    /*
     * query scope
     * A deal in publish status
     */
    public function scopePublish($query)
    {
        return $query->where('status', 'publish');
    }

}