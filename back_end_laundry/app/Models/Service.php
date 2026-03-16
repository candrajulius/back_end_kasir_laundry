<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'service_name',
        'pricing_type',
        'estimated_days',
        'is_active',
        'price',
    ];

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
