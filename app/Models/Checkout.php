<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Account;

class Checkout extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'status',
        'total_price',
        'order_id',
        'cart_id',
        'payment_price',
        'checkoutable_id',
        'checkoutable_type'
    ];

    public function checkoutable() {
        return $this->morphTo();
    }

    public function account() {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
