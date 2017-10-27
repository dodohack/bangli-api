<?php
/**
 * Attachments: includes image, file etc, utilizing MySQL 5.7 JSON type.
 */

namespace App\Models;

/*
|--------------------------------------------------------------------------
| Attachment, include all kind of uploaded files except images used by
| post, topic, page and product.
|--------------------------------------------------------------------------
|
|
*/
class Attachment extends EntityModel
{
    protected $table = 'attachments';

    public function simpleColumns()
    {
        return ['attachments.id', 'path', 'thumb_path', 'mime_type',
            'title', 'desc', 'filename', 'size', 'width', 'height',
            'thumbnail', 'created_at', 'updated_at'];
    }

    public function fullColumns()
    {
        return $this->simpleColumns();
    }

    public function simpleRelations()
    {
        return null;
    }

    public function fullRelations()
    {
        return null;
    }

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
