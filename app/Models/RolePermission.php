<?php

namespace App\Models;

use App\Traits\BelongsToShop;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use BelongsToShop;

    protected $fillable = ['shop_id', 'role', 'permission_key', 'is_allowed'];

    protected $casts = [
        'is_allowed' => 'boolean',
    ];
}
