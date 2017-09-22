<?php
/**
 * Attachments: includes image, file etc, utilizing MySQL 5.7 JSON type.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
|--------------------------------------------------------------------------
| Attachment, include all kind of uploaded files except images used by
| post, topic, page and product.
|--------------------------------------------------------------------------
|
|
*/
class Attachment extends Model
{
    protected $table = 'attachments';

    /*
     * Get the user owns this attachment
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /*
     * Attachment use Cms\Tag as a search filter.
     */
    public function tags()
    {
        return $this->hasOne('App\ModelsTag');
    }
}
