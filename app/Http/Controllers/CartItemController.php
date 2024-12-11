<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\CartItem;
use App\Models\Cart;
use App\Models\Product;
use App\Helpers\ResponseFormatter;

class CartItemController extends Controller
{

    public function addItemToCart(Request $request, int $userId) {
        try {
            
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer'
            ]);

            if($validator->fails()) {
                return ResponseFormatter::erorr(null, 'Input Error', 300);
            }

            $cart = Cart::firstOrCreate(['user_id', $userId]);

            $product = Product::find($userId);

            $cart->items->create([
                'cart_id' => $cart->id,
                'productable_id' => $product->id,
                'productable_type' => Product::class,
                'quantity' => $request->quantity,
                'price' => $product->price
            ]);

            return ResponseFormatter::success(null, "Success added To Cart");

        } catch (Exception $exception) {
            return ResponseFormatter::error(null, $exception->get_message());
        }
    }

    public function updateItemOnCart(Request $request, int $userId) {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer'
        ]);

        if($validator->fails()) {
            return ResponseFormatter::erorr(null, 'Input Error', 300);
        }

        $result = $cart->item->where('productable_id', $userId)->update([
            'quantity' => $request->quantity
        ]);
        
        return ResponseFormatter::success(null, 'Success update cart');
    }
}
