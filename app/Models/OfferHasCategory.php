<?php
/**
 * Relationship between offer and category.
 * This modal is only used by CmsCatController to update massive relationship
 * between offer and category.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferHasCategory extends Model
{
    protected $table = 'offer_has_category';
    protected $hidden = ['pivot'];
    public $timestamps = false;
}
