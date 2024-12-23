<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\CartItem;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code_product',
        'name',
        'description',
        'product_types_id',
        'photo',
        'price',
        'rate'
    ];

    public function type() {
        return $this->belongsTo(ProductType::class, 'product_types_id');
    }

    public function cartItem() {
        return $this->morphMany(CartItem::class, 'productable');
    }
}
