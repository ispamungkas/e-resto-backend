<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductType;
use Illuminate\Http\UploadedFile;

class ProductTypeController extends Controller
{
    public function all(Request $request) {
        $data = ProductType::all();
        return ResponseFormatter::success($data, 'Success');
    }

    public function store(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
                'name' => 'required|string'
            ]);
    

            if ($validate->fails()) {
                return ResponseFormatter::error(null, "Input Error" , 300);
            }
            
         
            $type = ProductType::create([
                'name' => $request->name,
            ]);
    
            return ResponseFormatter::success($type, 'Type of product was added');

        } catch (Exception $ex) {
            return ResponseFormatter::error(null, 'Internal Server Error' , 500);
        }
    }

    public function remove(Request $request, int $id) {
        $data = ProductType::findOrFail($id)->delete();
        return ResponseFormatter::success([
            'message' =>  $data
        ], "Delete Successfully");
    }
}
