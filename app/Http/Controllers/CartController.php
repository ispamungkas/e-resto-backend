<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Models\Cart;

class CartController extends Controller
{
    public function all(Request $request) {
        $userId = $request->input('userId');

        $data = Cart::where('user_id', $userId)->with(['items.productable', 'account'])->first();

        return ResponseFormatter::success($data, 'Success fetched');
    }

    public function delete(Request $request) {
        $userId = $request->input('userId');

        $cart = Cart::where('user_id', $userId)->first(); // Ambil cart dengan id 1
        $cart->delete(); 

        return ResponseFormatter::success($cart, 'Success deleted cart');
    }
}
