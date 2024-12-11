<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\CartItem;
use App\Models\Checkout;

class Cart extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id'
    ];

    public function items() {
        return $this->hasMany(CartItem::class, 'cart_id');
    }   

    public function checkout(): MorphOne {
        return $this->morphOne(Checkout::class, 'checkoutable');
    }
}
