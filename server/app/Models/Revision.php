<?php
/**
 * Revision Model: Draft storage for post/topic etc.
 * This is a polymorphic table
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    protected $table = 'revisions';
    // TODO: Commented out for first time debug
    //protected $hidden = ['body', 'pivot'];
    protected $fillable = ['id', 'state', 'user_id', 'body'];

    /*
     * Get the user that owns the revision
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /*
     * Get all of the owning content models
     */
    public function content()
    {
        return $this->morphTo();
    }
}
