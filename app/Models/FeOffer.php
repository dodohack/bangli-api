<?php
/**
 * Frontend offer view
 */

namespace App\Models;


class FeOffer extends Offer
{
    protected $table = 'fe_view_offers';

    /*
     * Return an array of columns which are returned to client when request
     * multiple offers.
     */
    public function simpleColumns()
    {
        return ['fe_view_offers.id', 'fe_view_offers.channel_id',
            'status', 'featured', 'title', 'display_url', 'tracking_url',
            'vouchers', 'starts', 'ends'];
    }

    /*
     * Return an array of columns which are returned to client when request
     * a single post.
     */
    public function fullColumns()
    {
        return $this->simpleColumns();
    }

    public function simpleRelations()
    {
        // All relations are needed by default
        return null;
    }

    public function fullRelations()
    {
        // All relations are needed by default
        return ['topic'];
    }

}
