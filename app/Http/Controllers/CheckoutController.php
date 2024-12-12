<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Cart;
use App\Models\Checkout;
use App\Helpers\RespnoseFormatter;
use App\Services\MidtransService;

class CheckoutController extends Controller
{

    protected $midtransService;

    public function __construct(MidtransService $midtransService) {
        $this->midtransService = $midtransService;
    }


    public function all(Request $request) {
        $userId = $request->input('userId');

        $checkout_data = Checkout::where('account_id', $userId)->with('checkoutable');

        return ResponseFormatter::success($checkout_data, 'Successfully Fetch');
    }

    public function checkout(Request $request, int $cartId) {

        try {
            $cart = Cart::with('items')->find($cartId);
            $user = Account::find($cart->user_id);

            $total_price = $cart->items->sum(function ($item) {
                return $item->price * $item->quantity;
            });

            $order_id = 'Order'.time();
            $params = [
                'transaction_detail' => [
                    'order_id' => $order_id,
                    'gross_amount' => (int) $total_price
                ],
                'customer_detail' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone_number,
                ]
            ];

            $snap_token = $this->midtransService->createTransaction($params);

            $checkout = Checkout::create([
                'account_id' => $cart->user_id,
                'status' => 'pending',
                'total_price_payment' => $total_price,
                'cart_id' => $cart->id,
                'payment_price' => 0,
                'checkoutable_id' => $cart->id,
                'checkoutable_type' => Cart::class
            ]);

            return ResponseFormatter::success([
                'snap_token' => $snap_token->token,
                'redirect_payment' => $snap_token->redirect_url
            ], "Checkout successfully");

        } catch (Exception $exception) {
            return ResponseFormatter::error(null, $exception->get_message());
        }
    }

    public function handleNotification(Request $request) {
        $notification = $request->all();
        $status = 'pending';

        $transactionStatus = $notif['transaction_status'];
        $orderId = $notif['order_id'];
        $paymentType = $notif['payment_type'];
        $fraudStatus = $notif['fraud_status'] ?? null;

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $status = 'challenge';
            } else {
                $status = 'success';
            }
        } elseif ($transactionStatus == 'settlement') {
            $status = 'success';
        } elseif ($transactionStatus == 'pending') {
            $status = 'pending';
        } elseif ($transactionStatus == 'deny') {
            $status = 'denied';
        } elseif ($transactionStatus == 'expire') {
            $status = 'expired';
        } elseif ($transactionStatus == 'cancel') {
            $status = 'canceled';
        } else {
            $status = 'unknown';
        }

        $checkout = Checkout::where('order_id', $order_id)->first();
        if ($checkout) {
            $checkout->update(['status' => $status]);
        } else {
            return ResponseFormatter::error(null, 'Transaction not found', 404);
        }

        \Log::info('Midtrans Notification: ', $request->all());

        return ResponseFormatter::success(null, 'Payement success');

    }
}
