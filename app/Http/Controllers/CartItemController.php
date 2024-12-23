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
                'quantity' => 'required|integer',
                'product_id' => 'required|integer'
            ]);

            if($validator->fails()) {
                return ResponseFormatter::erorr(null, 'Input Error', 300);
            }

            $cart = Cart::firstOrCreate(['user_id' => $userId]);

            $product = Product::find($request->product_id);
            $total_price = $request->quantity * $product->price;

            $result = CartItem::create([
                'cart_id' => $cart->id,
                'productable_id' => $product->id,
                'productable_type' => (string) Product::class,
                'quantity' => (int) $request->quantity,
                'price' => $total_price
            ]);

            return ResponseFormatter::success($result, "Success added To Cart");

        } catch (Exception $exception) {
            return ResponseFormatter::error(null, $exception->getMessage());
        }
    }

    public function deleteProductFromCart(Request $request) {
        $validator = Validator::make($request->all(), [
            'cart_item_id' => 'required|integer'
        ]);

        if($validator->fails()) {
            return ResponseFormatter::erorr(null, 'Input Error', 300);
        }

        $cartItem = CartItem::where('id', $request->cart_item_id);
        $cartItem->update([
            'cart_id' => 0,
        ]);
        $cartItem->delete();

        return ResponseFormatter::success(null, 'Success update cart');
    }

    public function updateItemOnCart(Request $request) {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer',
            'cart_item_id' => 'required|integer'
        ]);

        if($validator->fails()) {
            return ResponseFormatter::erorr(null, 'Input Error', 300);
        }

        $cartItem = CartItem::where('id', $request->cart_item_id)->with(['productable'])->first();
        $amount = $request->quantity * $cartItem->productable->price;
        $cartItem->update([
            'quantity' => $request->quantity,
            'price' => $amount
        ]);
        
        return ResponseFormatter::success(null, 'Success update cart');
    }
}
