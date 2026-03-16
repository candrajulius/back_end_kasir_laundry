<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promo extends Model
{
    //
    use HasFactory;

    protected $fillable = ['promo_code', 'description', 'discount_type', 'discount_value', 'start_date', 'end_date', 'is_active'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
