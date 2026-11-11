<?php

namespace App\Models;

use App\Traits\BelongsToShop;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use BelongsToShop;

    protected $fillable = [
        'shop_id',
        'phone',
        'tax_rate',
        'discount_threshold',
        'printer_ip',
        'auto_print',
    ];

    protected $casts = [
        'auto_print' => 'boolean',
        'tax_rate' => 'integer',
        'discount_threshold' => 'integer',
    ];
}
