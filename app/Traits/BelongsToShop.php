<?php

namespace App\Traits;

use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;

trait BelongsToShop
{
    protected static function bootBelongsToShop()
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (Auth::check()) {
                $model->shop_id = Auth::user()->shop_id;
            }
        });
    }

    public function shop()
    {
        return $this->belongsTo(\App\Models\Shop::class);
    }
}
