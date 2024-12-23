<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\CartItem;
use App\Models\Account;
use App\Models\Checkout;

class Cart extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id'
    ];

    public function items() {
        return $this->hasMany(CartItem::class, 'cart_id')->withTrashed();
    }   

    public function checkout(): MorphOne {
        return $this->morphOne(Checkout::class, 'checkoutable');
    }

    public function account() {
        return $this->belongsTo(Account::class, 'user_id', 'id');
    }
    

    protected static function boot() {
        parent::boot();

        static::deleting(function ($cart) {
            $cart->items()->delete(); // Soft delete semua item terkait
        });
    }

}
