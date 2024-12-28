<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseFormatter;

class ProductController extends Controller
{
    public function all(Request $request) {
        $data = Product::orderBy('created_at', 'desc')->get();

        return ResponseFormatter::success($data, 'Successfully Fetched');
    }

    public function add(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
                'name' => 'required|string|max:50',
                'description' => 'required|string|min:24',
                'product_types_id' => 'required|string',
                'price' => 'required|string',
                'photo' => 'required|file|mimes:jpg,png,jpeg',
                'rate' => 'required'
            ]);

            if ($validate->fails()) {
                return ResponseFormatter::error($validate->messages()->get('*'), "Input Error" , 300);
            }
            
            // Generate code product
            $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            $code_product = substr(str_shuffle($str_result), 0, 10);

            // Store photo of product
            $file = $request->file('photo');
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->move(public_path('images'), $fileName);
    
            // Casting value
            $ci_price = (integer) $request->price;
            $ci_product_types_id = (integer) $request->product_types_id;


            $data = Product::create([
                'code_product' => $code_product,
                'name' => $request->name,
                'description' => $request->description,
                'rate' => $request->rate,
                'product_types_id' => $ci_product_types_id,
                'price' => $ci_price,
                'photo' => "/images/".$fileName
            ]);

            return ResponseFormatter::success($request->all(), "Product Successfully Added");

        } catch (Exception $ex) {
            return ResponseFormatter::error($ex->getMessage(), 'Internal Server Error' , 500);
        }
    }

    public function deleteProduct(Request $request) {
        $id = $request->productId;
        $data = Product::where('id', $id)->first();
        $data->delete();
        return ResponseFormatter::success($data, 'Delete succesfully');
    }

    public function updateProduct(Request $request) {

        try {
            $validate = Validator::make($request->all(), [
                'productId' => 'required',
                'price' => 'required',
            ]);

            if ($validate->fails()) {
                return ResponseFormatter::error(null, "Input Error" , 300);
            }

            $id = $request->productId;
            $data = Product::where('id', $id)->first();
            $data->update([
                'price' => $request->price
            ]);

            return ResponseFormatter::success($data, 'Delete succesfully');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Input Error" , 500);
        }
        
    }
}
