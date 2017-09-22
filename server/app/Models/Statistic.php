<?php
/**
 * Statistic Model: All kind of statistics of post/topic etc.
 * This is a polymorphic table
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    protected $table = 'statistics';
    protected $fillable = ['id', 'state', 'word_count', 'view', 'share',
        'comment', 'upvote', 'downvote'];

    /**
     * The polymorphic table is distinguished by content_type and content_id
     */
    public function content()
    {
        return $this->morphTo();
    }
}
