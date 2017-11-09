<?php
/**
 * Advertisement model
 */

namespace App\Models;

class FeAdvertise extends Advertise
{
    protected $table = 'fe_view_ads';

    public function simpleColumns()
    {
        return ['fe_view_ads.id', 'channel_id', 'status', 'position', 'location',
            'rank', 'title', 'image_url', 'target_url', 'starts', 'ends'];
    }

    public function fullColumns()
    {
        // All columns are needed by default
        return null;
    }
}