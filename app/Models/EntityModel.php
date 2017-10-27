<?php
/**
 * Base model for all entity models
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class EntityModel extends Model
{
    // All entity models should implement these functions
    abstract public function simpleColumns();
    abstract public function fullColumns();
    abstract public function simpleRelations();
    abstract public function fullRelations();
}
