<?php
/**
 * Menu Model : All kind of menus used by frontend and backend website.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    /* Table name */
    protected $table = 'menus';

    protected $fillable = ['parent_id', 'device', 'type', 'name', 'url',
        'group', 'order', 'external', 'icon', 'style'];

    /* Don't have timestamp entry for this model */
    public $timestamps = false;

    /*
     * Frontend desktop menu
     */
    public function scopeDesktopMenu($query)
    {
        return $query->where('device', 'DESKTOP');
    }

    /*
     * Frontend mobile menu
     */
    public function scopeMobileMenu($query)
    {
        return $query->where('device', 'MOBILE');
    }
}
