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
    public function register_user(Request $request)
    {
        $message_error = [
            'name.required' => 'Nama wajib isi',
            'email.required' => 'Email wajib isi',
            'role_id.required' => 'Role wajib isi',
            'email.email' => 'Email harus valid',
            'password.required' => 'Password wajib isi',
            'email.min' => 'Email minimal harus 8 karakter'
        ];

        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'role_id' => 'required',
            'password' => 'required|min:8'
        ], $message_error);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'role_id' => $request->role_id,
            'salary' => $request->salary,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi Berhasil',
            'data' => $user,
            'code' => 201
        ]);
    }

    public function register_customer(Request $request)
    {
        $message_error = [
            'name.required' => 'Nama wajib isi',
            'email.required' => 'Email wajib isi',
            'email.email' => 'Email harus valid',
            'phone_number.required' => 'Nomor Telepon wajib isi',
            'address.required' => 'Alamat wajib isi',
            'password.required' => 'Password wajib isi',
            'email.min' => 'Email minimal harus 8 karakter',
            'phone_number.min' => 'Nomor Telepon minimal harus 12 karakter'
        ];

        $request->validate([
            'name' => 'required',
            'address' => 'required',
            'phone_number' => 'required|min:12',
            'email' => 'required|email',
            'password' => 'required|min:8'
        ], $message_error);

        $customer = Customer::create([
            'name' => $request->name,
            'address' => $request->address,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi Berhasil',
            'data' => $customer,
            'code' => 201
        ]);
    }

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
