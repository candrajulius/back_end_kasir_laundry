<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $services = Service::paginate(10);
        return response()->json([
            'success' => true,
            'message' => 'List Data Service',
            'data' => $services,
            'code' => 200
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $message_error = [
            'service_name.required' => 'Nama Service wajib isi',
            'pricing_type.required' => 'Tipe Harga wajib isi',
            'pricing_type.in' => 'Tipe Harga harus 0 (per unit) atau 1 (per jam)',
            'price.required' => 'Harga wajib isi',
            'price.numeric' => 'Harga harus berupa angka',
            'estimated_days.required' => 'Estimasi Hari wajib isi',
            'estimated_days.integer' => 'Estimasi Hari harus berupa angka bulat',
        ];

        $request->validate([
            'service_name' => 'required',
            'pricing_type' => 'required|in:0,1',
            'price' => 'required|numeric',
            'estimated_days' => 'required|integer',
        ], $message_error);

        $service = Service::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data Service berhasil ditambahkan',
            'data' => $service,
            'code' => 201
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $service = Service::find($id);
        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Data Service tidak ditemukan',
                'data' => null,
                'code' => 404
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data Service ditemukan',
            'data' => $service,
            'code' => 200
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $service = Service::find($id);
        if(!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Data Service tidak ditemukan',
                'data' => null,
                'code' => 404
            ]);
        }

        $message_error = [
            'service_name.required' => 'Nama Service wajib isi',
            'pricing_type.required' => 'Tipe Harga wajib isi',
            'pricing_type.in' => 'Tipe Harga harus 0 (per unit) atau 1 (per jam)',
            'price.required' => 'Harga wajib isi',
            'price.numeric' => 'Harga harus berupa angka',
            'estimated_days.required' => 'Estimasi Hari wajib isi',
            'estimated_days.integer' => 'Estimasi Hari harus berupa angka bulat',
        ];

        $request->validate([
            'service_name' => 'required',
            'pricing_type' => 'required|in:0,1',
            'price' => 'required|numeric',
            'estimated_days' => 'required|integer',
        ], $message_error);

        $service->update([
            'service_name' => $request->service_name,
            'pricing_type' => $request->pricing_type,
            'price' => $request->price,
            'estimated_days' => $request->estimated_days,
            'is_active' => $request->is_active ?? $service->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Service berhasil diupdate',
            'data' => $service,
            'code' => 200
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $service = Service::find($id);
        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Data Service tidak ditemukan',
                'data' => null,
                'code' => 404
            ]);
        }

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Service berhasil dihapus',
            'data' => null,
            'code' => 200
        ]);

    }
}
