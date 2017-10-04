<?php
/**
 * Editor view from table users with role id in 1, 2, 3
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewEditor extends Model
{
    protected $table = 'view_editors';
    protected $hidden = ['pivot'];
    public $timestamps = false;
}
