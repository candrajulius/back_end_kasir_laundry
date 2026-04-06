<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{


    use HasFactory;

    protected $fillable = [
        'invoice_number', 'customer_id', 'user_id', 'promo_id', 'status', 'payment_status',
        'subtotal', 'discount_amount', 'tax_amount', 'total_amount', 'notes',
        'received_at', 'completed_at'
    ];

    const STATUS_PENDING = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_FAILED = 99;

    const PAYMENT_PENDING = 0;
    const PAYMENT_PAID = 1;
    const PAYMENT_FAILED = 99;

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function promos()
    {
        return $this->hasMany(Promo::class);
    }
}
