<?php

namespace App\Models;

use App\Traits\BelongsToShop;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use BelongsToShop;

    protected $fillable = [
        'shop_id', 
        'user_id', 
        'customer_name', 
        'invoice_number',
        'total_price', 
        'status', 
        'payment_method',
        'paid_amount',
        'change_amount',
        'tax_amount',
        'discount_amount'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
