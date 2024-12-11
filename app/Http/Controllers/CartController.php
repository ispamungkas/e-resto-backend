<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\ResponseFormatter;
use App\Models\Cart;

class CartController extends Controller
{
    public function all(Request $request) {
        $userId = $request->input('userId');

        $data = Cart::where('user_id', $userId)->with('items')->first();

        return ResponseFormatter::success($data, 'Success fetched');
    }
}
