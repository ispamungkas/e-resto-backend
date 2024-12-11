<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Excecption;
use Illuminate\Support\Facades\Validator;
use App\Models\PaymentMethod;
use App\Helpers\ResponseFormatter;

class PaymentMethodController extends Controller
{
    public function all(Request $request) {
        $data = PaymentMethod::all();
        return ResponseFormatter::success($data, "Successfully Fetch");
    }

    public function store(Request $request) {
        try {
           $validate =  Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'photo' => 'required|file|mimes:jpeg,jpg,png',
                'label' => 'required|string|max:255'
            ]);

            if ($validate->fails()) {
                return ResponseFormatter::error(null, "Input Error", 300);
            }

            // Store photo of product
            $file = $request->file('photo');
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->move(public_path('images'), $fileName);

            $result = PaymentMethod::create([
                'name' => $request->name,
                'photo' => asset('images/' . $fileName),
                'label' => $request->label
            ]);

            return ResponseFormatter::success($result, "Payment Method Was Added");
    
        } catch (Exception $ex) {
            return ResponseFormatter::error(null, "Internal Server Error", 500);
        }
    }
}
