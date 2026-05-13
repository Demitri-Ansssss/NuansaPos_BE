<?php

namespace App\Models;

use App\Traits\BelongsToShop;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use BelongsToShop;

    protected $fillable = [
        'shop_id',
        'name',
    ];
}
