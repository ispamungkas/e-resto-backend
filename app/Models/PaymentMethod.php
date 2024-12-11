<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Checkout;

class PaymentMethod extends Model
{

    protected $fillable = [
        'name', 'photo', 'label'
    ];

    public function checkouts() {
        return $this->hashMany(Checkout::class, 'payment_id', 'id');
    }

}
