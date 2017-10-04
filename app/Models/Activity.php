<?php
/**
 * Activity Model: All kind of activities of post/topic/product etc.
 * This is a polymorphic table
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'activities';
    protected $fillable = ['id', 'user_id', 'edit_lock'];

    /**
     * The polymorphic table is distinguished by content_type and content_id
     */
    public function content()
    {
        return $this->morphTo();
    }

    /**
     * FIXME: Hardcoded string!
     * Return 'content_type' column name based on input more readable name,
     * 'content_type' matches the definition in AppServiceProvider.php.
     */
    static public function getContentType($type)
    {
        switch ($type) {
            case 'post':
                return 'post';
            case 'page':
                return 'page';
            case 'topic':
                return 'topic';
            case 'newsletter':
                return 'newsletter';
            default:
                return null;
        }

    }
}
