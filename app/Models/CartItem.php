<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Cart;

class CartItem extends Model
{
   
    protected $fillable  = [
        'cart_id', 'productable_id', 'productable_type',
        'quantity', 'price'
    ];

    public function productable(): MorphTo {
        return $this->morphTo();
    }

    public function cart() {
        return $this->belongsTo(Cart::class, 'cart_id');
    }
}
