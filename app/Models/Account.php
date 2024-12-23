<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use TwoFactorAuthenticatable;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'role',
        'password'
    ];

    protected $hidden = [
        'password'
    ];
    
    public function carts() {
        return $this->hasOne(Cart::class, 'user_id');
    }
}
