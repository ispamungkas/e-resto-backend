<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductType extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public function products() {
        return $this->hashMany(Product::class, 'product_types_id');
    }
}
