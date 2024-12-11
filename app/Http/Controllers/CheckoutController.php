<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Cart;
use App\Models\Checkout;
use App\Helpers\RespnoseFormatter;

class CheckoutController extends Controller
{
    public function checkout(Request $request, int $cartId) {
        try {
            $cart = Cart::with('items')->find($cartId);

            $total_price = $cart->items->sum(function ($item) {
                return $item->price * $item->quantity;
            });

            $checkout = Checkout::create([
                'account_id' => $cart->user_id,
                'status' => 'Pending',
                'total_price_payment' => $total_price,
                'cart_id' => $cart->id,
                'payment_price' => 0,
                'checkoutable_id' => $cart->id,
                'checkoutable_type' => Cart::class
            ]);

            return ResponseFormatter::success($checkout, "Checkout successfully");

        } catch (Exception $exception) {
            return ResponseFormatter::error(null, $exception->get_message());
        }
    }
}
