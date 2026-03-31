<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $promos = Promo::where('is_active', true)
            ->whereDate('start_date', '<=', Carbon::today())
            ->whereDate('end_date', '>=', Carbon::today())
            ->whereDoesntHave('transactions', function ($query) {
                $query->where('customer_id', auth('customer')->id())
                    ->where('paymnent_status', 1); // misalnya 1 = paid
            })
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar promo yang tersedia',
            'data' => $promos,
            'code' => 200
        ]);
    }

    public function apply_promo(Request $request)
    {
        //
        $customer = auth('customer')->user();
        $promo = Promo::where('promo_code', $request->promo_code)->first();

        if (!$promo){
            return response()->json(([
                'success' => false,
                'message' => 'Kode promo tidak valid',
                'code' => 404
            ]));
        }

        if (!$promo->is_active){
            return response()->json(([
                'success' => false,
                'message' => 'Kode promo tidak aktif',
                'code' => 400
            ]));
        }

        if ($promo->end_date < Carbon::today()){
            return response()->json(([
                'success' => false,
                'message' => 'Kode promo sudah expired',
                'code' => 400
            ]));
        }

        $alreadyUsed = Transaction::where('customer_id', $customer->id)
            ->where('promo_id', $promo->id)
            ->where('paymnent_status', 1) // misalnya 1 = paid
            ->exists();
        
        if ($alreadyUsed){
            return response()->json(([
                'success' => false,
                'message' => 'Kode promo sudah pernah digunakan',
                'code' => 400
            ]));
        }

        return response()->json(([
            'success' => true,
            'message' => 'Kode promo berhasil diterapkan',
            'data' => $promo,
            'code' => 200
        ]));
        // if ($promo->start_date > now() || $promo->end_date < now()){
        //     return response()->json(([
        //         'success' => false,
        //         'message' => 'Kode promo tidak aktif',
        //         'code' => 400
        //     ]));
        // }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'promo_code' => 'required|unique:promos,promo_code',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:0,1', // 0 = percentage, 1 = fixed
            'discount_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean'
        ]);

        // Simpan promo baru
        $promo = Promo::create([
            'promo_code' => $request->promo_code,
            'description' => $request->description,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->is_active ?? true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Promo berhasil dibuat',
            'data' => $promo,
            'code' => 201
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
