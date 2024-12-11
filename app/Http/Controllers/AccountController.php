<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Models\Account;
use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function login(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|max:255',
                'password' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(null, "Input Error" , 300);
            }

            $account = Account::where('email', $request->email)->first();

            // Check if the password doesn't match
            if(!Hash::check($request->password, $account->password, [])){
                throw new \Exception('Invalid Credentials');
            };

            $token = $account->createToken('authToken')->plainTextToken;
            
            return ResponseFormatter::success([
                'token_type' => 'Bearer',
                'token' => $token,
                'data' => $account
            ], 'Register Success');

        } catch (Exception $ex) {
            return ResponseFormatter::error([
                'message' => "'Something wen't wrong",
                'error' => $ex 
            ], 'Authentication Failed' , 500);
        }
    }

    public function register(Request $request) {
        try {
            
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|unique:accounts,email|email|max:255',
                'password' => 'required|string|max:255',
                'name' => 'required|string|max:25',
                'phone_number' => 'required|string|max:17',
                'role' => 'required|string'
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(null, "Input Error" , 300);
            }
            
            Account::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'role' => $request->role,
                'name' => $request->name
            ]);
    
            $account = Account::where('email', $request->email)->first();
            // $token = $account->createToken('authToken')->plainTextToken;
    
            return ResponseFormatter::success([
                'message' => 'Success',
                'data' => $account
            ], 'Register Success');
    
       } catch (Exception $ex) {
            return ResponseFormatter::error([
                'message' => "'Something wen't wrong",
                'error' => $ex 
            ], 'Authentication Failed' , 500);
       }
    }

}
