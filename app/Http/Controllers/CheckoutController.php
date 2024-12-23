<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Cart;
use App\Models\Checkout;
use App\Models\Account;
use App\Helpers\RespnoseFormatter;
use App\Helpers\ResponseFormatter;
use App\Services\MidtransService;
use Midtrans\Config;
use Midtrans\Snap;


class CheckoutController extends Controller
{

    protected $midtransService;

    public function __construct(MidtransService $midtransService) {
        $this->midtransService = $midtransService;
    }


    public function all(Request $request) {
        $userId = $request->input('userId');

        $checkout_data = Checkout::where('account_id', $userId)->with('checkoutable')->orderBy('created_at', 'desc')->get();

        return ResponseFormatter::success($checkout_data, 'Successfully Fetch');
    }

    public function allCheckout(Request $request) {
        $checkout = Checkout::orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($checkout, 'Success fetch');
    }

    public function checkoutById(Request $request) {
        $checkoutableId = $request->input('checkoutableId');

        $items = Cart::withTrashed()->where('id', $checkoutableId)->with(['items.productable', 'account'])->first();
        
        return ResponseFormatter::success($items, 'Successfull fetch');
    }


    public function checkout(Request $request, int $cartId) {
 
        try {
            $cart = Cart::with(['items.productable'])->find($cartId);
            $user = Account::find($cart->user_id);

            $total_price = $cart->items->sum(function ($item) {
                return $item->price * $item->quantity;
            });

            $order_id = $order_id = uniqid('ORDER-');

            $item_detail = [];
            foreach($cart->items as $item) {
                $item_detail[] = [
                    'id' => $item,
                    'name' => $item->productable->name,
                    'quantity' => $item->quantity ,
                    'price' => $item->productable->price
                ];
            }

            $params = [
                'transaction_details' => [
                    'order_id' => $order_id,
                    'gross_amount' => $total_price
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone_number,
                ],
                'item_details' => $item_detail,
                'callbacks' => [
                    'finish' => 'yourapp://payment-finish?status=success',
                    'error' => 'yourapp://payment-finish?status=success',
                    'unfinish' => 'yourapp://payment-finish?status=success'
                ],
            ];

            $snap = $this->midtransService->createTransaction($params);

            $checkout = Checkout::firstOrCreate([
                'account_id' => $user->id,
                'status' => 'pending',
                'order_id'=> $order_id,
                'payment_price' => 0,
                'checkoutable_id' => $cart->id,
                'checkoutable_type' => Checkout::class,
                'total_price' => $total_price,
            ]);
            $cart->delete();

            return ResponseFormatter::success([
                'snap_token' => $snap->token,
                'redirect_payment' => $snap->redirect_url
            ], "Checkout successfully");

        } catch (Exception $exception) {
            return ResponseFormatter::error(null, $exception->getMessage());
        }
    }

    public function repayCheckout(Request $request, int $cartId) {
        try {
            $checkout = Checkout::with(['checkoutable', 'account'])->where('checkoutable_id', $cartId)->first();

            $order_id = $order_id = uniqid('ORDER-');

            $params = [
                'transaction_details' => [
                    'order_id' => $order_id,
                    'gross_amount' => $checkout->total_price
                ],
                'customer_details' => [
                    'first_name' => $checkout->account->name,
                    'email' => $checkout->account->email,
                    'phone' => $checkout->account->phone_number,
                ],
                'callbacks' => [
                    'finish' => 'yourapp://payment-finish?status=success',
                    'error' => 'yourapp://payment-finish?status=success',
                    'unfinish' => 'yourapp://payment-finish?status=success'
                ],
            ];

            $snap = $this->midtransService->createTransaction($params);    
        
            $result = $checkout->update([
                'account_id' => $checkout->account->id,
                'status' => 'pending',
                'order_id'=> $order_id,
                'payment_price' => 0,
                'checkoutable_id' => $checkout->checkoutable_id,
                'checkoutable_type' => Checkout::class,
                'total_price' => $checkout->total_price,
            ]);   
            return ResponseFormatter::success([
                'snap_token' => $snap->token,
                'redirect_payment' => $snap->redirect_url
            ], "Checkout successfully");
        } catch (Exception $exception) {
            return ResponseFormatter::error(null, $exception->getMessage());
        }
    }

    public function handleNotification(Request $request) {
        $notification = $request->all();
        $status = 'pending';

        $transactionStatus = $notification['transaction_status'];
        $orderId = $notification['order_id'];
        $paymentType = $notification['payment_type'];
        $fraudStatus = $notification['fraud_status'] ?? null;

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $status = 'pending';
            } else {
                $status = 'process';
            }
        } elseif ($transactionStatus == 'settlement') {
            $status = 'process';
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

        $checkout = Checkout::where('order_id', $orderId)->first();
        if ($checkout) {
            $checkout->update(['status' => $status, 'payment_price' => $checkout->total_price]);
        } else {
            return ResponseFormatter::error(null, 'Transaction not found', 404);
        }

        \Log::info('Midtrans Notification: ', $request->all());

        return ResponseFormatter::success(null, 'Payment success');
    }

    public function updateStatus(Request $request) {
        $status = $request->status;
        $checkout = Checkout::where('checkoutable_id', $request->input('checkoutableId'))->first();
        $checkout->update([
            'status' => $status
        ]);

        return ResponseFormatter::success($checkout, 'Berhasil terupdate');
        
    }
}
