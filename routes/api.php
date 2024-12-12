<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Helpers\ResponseFormatter;
use App\Models\Account;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Middleware\CheckAuthorizationHeader;
use Illuminate\Support\Facades\Hash;



Route::post('/register', [AccountController::class, 'register']);
Route::post('/login', [AccountController::class, 'login']);
Route::post('/checkout/update', [CheckoutController::class, 'handleNotification']);

Route::middleware(['check'])->group(function() {
    /**
     * Product Routinng
     */
    Route::get('/product', [ProductController::class, 'all']);
    Route::post('/product/add', [ProductController::class, 'add']);


    /**
     * Product Type Routing
     */
    Route::get('/producttype/all', [ProductTypeController::class, 'all']);
    Route::post('/producttype/store', [ProductTypeController::class, 'store']);
    Route::delete('/producttype/remove/{id}', [ProductTypeController::class, 'remove']);


    /**
     * Payment Method Routing
     */

    Route::get('/paymentmethod/all', [PaymentMethodController::class, 'all']);
    Route::post('/paymentmethod/add', [PaymentmethodController::class, 'store']);

    /**
     * Cart Routing
     */

    Route::get('/cart', [CartController::class, 'all']);
    Route::post('/cart/add/{userId}', [CartItemController::class, 'addItemToCart']);
    Route::put('/cart/update/{userId}', [CartItemController::class, 'updateItemOnCart']);

    /**
     * Checkout Routing
     */
    Route::get('/checkout', [CheckoutController::class, 'all']);
    Route::post('/checkout/{cartId}', [CheckoutController::class, 'checkout']);
   

});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
