<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Cart;

class CartItem extends Model
{
   
    use SoftDeletes;

    protected $fillable  = [
        'cart_id', 'productable_id', 'productable_type',
        'quantity', 'price'
    ];

    public function productable(): MorphTo {
        return $this->morphTo()->withTrashed();
    }

    public function cart() {
        return $this->belongsTo(Cart::class, 'cart_id');
    }
}
