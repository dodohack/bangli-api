<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferFilter extends Model
{
    protected $table = 'offer_filters';
    protected $fillable = ['name', 'type', 'content'];
}
