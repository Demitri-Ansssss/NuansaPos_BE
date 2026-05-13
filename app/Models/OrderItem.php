<?php

namespace App\Models;

use App\Traits\BelongsToShop;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use BelongsToShop;

    protected $fillable = ['shop_id', 'order_id', 'product_id', 'quantity', 'price', 'subtotal', 'notes'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
