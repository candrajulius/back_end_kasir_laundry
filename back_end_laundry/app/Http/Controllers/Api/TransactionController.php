<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Promo;
use App\Models\Service;
use Carbon\Carbon;
use App\Models\TransactionItem;
use App\Models\Payment;


class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $transactions = Transaction::with('customer', 'user', 'transactionItems', 'payments', 'promos')
            ->where('customer_id', auth('customer')->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar transaksi Anda',
            'data' => $transactions,
            'code' => 200
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $customer = auth('customer')->user();

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.weight' => 'nullable|numeric|min:0',
            'promo_code' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {

            // 🔥 1. HITUNG SUBTOTAL DARI ITEMS
            $subtotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {

                $service = Service::findOrFail($item['service_id']);

                $qty = $item['quantity'] ?? 1;
                $weight = $item['weight'] ?? 0;

                $totalPrice = $service->price * $qty;

                $subtotal += $totalPrice;

                $itemsData[] = [
                    'service_id' => $service->id,
                    'quantity' => $qty,
                    'weight' => $weight,
                    'price' => $service->price,
                    'total_price' => $totalPrice
                ];
            }

            // 🔥 2. HANDLE PROMO (ULANGI VALIDASI)
            $promo = null;
            $discount = 0;

            if ($request->promo_code) {

                $promo = Promo::where('promo_code', $request->promo_code)->first();

                if (!$promo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kode promo tidak ditemukan',
                        'code' => 400
                    ]);
                }

                if (!$promo->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kode promo tidak aktif',
                        'code' => 400
                    ]);
                }

                if ($promo->end_date < Carbon::today()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kode promo sudah expired',
                        'code' => 400
                    ]);
                }

                // 🔥 FIX TYPO
                $alreadyUsed = Transaction::where('customer_id', $customer->id)
                    ->where('promo_id', $promo->id)
                    ->where('status', 2)
                    ->exists();

                if ($alreadyUsed) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kode promo sudah pernah digunakan',
                        'code' => 400
                    ]);
                }

                // 🔥 HITUNG DISKON
                // Percentage = 0, Fixed = 1
                if ($promo->discount_type == 0) {
                    $discount = ($promo->discount_value / 100) * $subtotal;
                } else {
                    $discount = $promo->discount_value;
                }

                $discount = min($discount, $subtotal);
            }

            // 🧾 3. TOTAL
            $tax = 0;
            $total = $subtotal - $discount + $tax;

            // 🔥 4. SIMPAN TRANSACTION
            $transaction = Transaction::create([
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'customer_id' => $customer->id,
                'user_id' => 1,
                'promo_id' => $promo?->id,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'status' => 0,
                'payment_status' => 0,
                'notes' => $request->notes,
                'received_at' => now()
            ]);

            // 🔥 5. SIMPAN ITEMS
            foreach ($itemsData as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    ...$item
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat',
                'data' => $transaction->load('transactionItems')
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $transaction = Transaction::with('customer', 'transactionItems.service', 'payments', 'promos')
            ->where('id', $id)
            ->where('customer_id', auth('customer')->id())
            ->first();
        if (!$transaction){
            return response()->json(([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
                'code' => 404
            ]));
        }

        return response()->json(([
            'success' => true,
            'message' => 'Detail transaksi',
            'data' => $transaction,
            'code' => 200
        ]));
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

    // Status Antrian
    public function queue()
    {
        $transaction = Transaction::where('status', Transaction::STATUS_PENDING)
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar transaksi dalam antrian',
            'data' => $transaction,
            'code' => 200
        ]);

    }

    // Status Proses
    public function processing()
    {
        $transaction = Transaction::where('status', Transaction::STATUS_PROCESSING)
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar transaksi dalam proses',
            'data' => $transaction,
            'code' => 200
        ]);
    }

    public function completed()
    {
        $transaction = Transaction::where('status', Transaction::STATUS_COMPLETED)
            ->orderBy('created_at', 'asc')
            ->paginate(10);
        return response()->json([
            'success' => true,
            'message' => 'Daftar transaksi yang sudah selesai',
            'data' => $transaction,
            'code' => 200
        ]);
    }

    public function take($id)
    {
        $transaction = Transaction::find($id);
        if (!$transaction){
            return response()->json(([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
                'code' => 404
            ]));
        }

        $transaction->status = Transaction::STATUS_PROCESSING;
        $transaction->user_id = auth()->id();
        $transaction->save();

        return response()->json([
            'success' => true,
            'message' => 'Transaksi diambil untuk diproses',
            'data' => $transaction,
            'code' => 200
        ]);
    }

    public function pay($id)
    {
        $transaction = Transaction::find($id);
        if (!$transaction){
            return response()->json(([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
                'code' => 404
            ]));
        }

        if ($transaction->payment_status == Transaction::PAYMENT_PAID){
            return response()->json(([
                'success' => false,
                'message' => 'Transaksi sudah dibayar',
                'code' => 400
            ]));
        }

        $request->validate([
            'payment_method' => 'required|in:cash,transfer,qris',
        ]);

        DB::beginTransaction();

        try{
            Payment::create([
                'transaction_id' => $transaction->id,
                'payment_method' => $request->payment_method,
                'amount' => $transaction->total_amount,
                'paid_at' => now(),
            ]);

            $transaction->update([
                'payment_status' => Transaction::PAYMENT_PAID,
                'status' => Transaction::STATUS_COMPLETED,
                'completed_at' => now(),
                'received_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibayar',
                'data' => $transaction,
                'code' => 200
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'code' => 400,
            ]);
        }
    }
}


