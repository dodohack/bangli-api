<?php
/**
 * Author view from table users with role id in 1, 2, 3, 4
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewAuthor extends Model
{
    protected $table = 'view_authors';
    protected $hidden = ['pivot'];
    public $timestamps = false;
}
