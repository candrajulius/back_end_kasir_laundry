<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        
        try{
            $customers = Customer::paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menampilkan data pelanggan',
                'data' => $customers,
                'code' => 200
            ]);   
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data' => null,
                'code' => 500
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        try{
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:customers',
                'password' => 'required|string|min:8',
                'phone_number' => 'required|string|max:20|unique:customers',
                'address' => 'required|string|max:255',
            ]);

            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'address' => $request->address,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Akun anda berhasil dibuat',
                'data' => $customer,
                'code' => 201
            ]);
        }catch(\Illuminate\Validation\ValidationException $e){
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $e->getMessage(),
                'data' => null,
                'code' => 422
            ]);
        }
        catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data' => null,
                'code' => 500
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        try
        {
            $customer = Customer::find($id);
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pelanggan tidak ditemukan',
                    'data' => null,
                    'code' => 404
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menampilkan data pelanggan',
                'data' => $customer,
                'code' => 200
            ]);
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data' => null,
                'code' => 500
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|max:255|unique:customers,email,' . $id,
                'password' => 'sometimes|required|string|min:8',
                'phone_number' => 'sometimes|required|string|max:20|unique:customers,phone_number,' . $id,
                'address' => 'sometimes|required|string|max:255',
            ]);

            $customer = Customer::findOrFail($id);

            if ($request->filled('password')) {
                $validated['password'] = Hash::make($request->password);
            }

            $customer->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Customer berhasil diupdate',
                'data' => $customer
            ], 200);

        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server',
            ], 500);

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
