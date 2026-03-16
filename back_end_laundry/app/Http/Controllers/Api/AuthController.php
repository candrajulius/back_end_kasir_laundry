<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login_user(Request $request)
    {
        $message_error = [
            'email.required' => 'Email wajib isi',
            'email.email' => 'Email harus valid',
            'password.required' => 'Password wajib isi',
            'email.min' => 'Email minimal harus 8 karakter'
        ];


        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8'
        ], $message_error);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password Salah', 
                'code' => 401
            ]);
        }

        $user = User::with('role')->where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;
        $token = explode('|',$token);
        $token = $token[1];

        return response()->json([
            'success' => true,
            'message' => 'Login Berhasil',
            'data' => [
                'user' => $user,
                'token' => $token
            ],
            'code' => 200
        ]);
    }

    public function logout_user(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout Berhasil',
            'code' => 200
        ]);
    }

    public function login_customer(Request $request)
    {
        $message_error = [
            'email.required' => 'Email wajib isi',
            'email.email' => 'Email harus valid',
            'password.required' => 'Password wajib isi',
            'email.min' => 'Email minimal harus 8 karakter'
        ];

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8'
        ], $message_error);



        $customer = Customer::where('email', $request->email)->firstOrFail();


        if (!$customer || !Hash::check($request->password, $customer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password Salah', 
                'code' => 401
            ]);
        }

        $token = $customer->createToken('customer_token')->plainTextToken;
        $token = explode('|',$token);
        $token = $token[1];

        return response()->json([
            'success' => true,
            'message' => 'Login Berhasil',
            'data' => [
                'customer' => $customer,
                'token' => $token
            ],
            'code' => 200
        ]);
    }

    public function logout_customer(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout Berhasil',
            'code' => 200
        ]);
    }
}
