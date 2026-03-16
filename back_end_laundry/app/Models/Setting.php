<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'store_name', 
        'store_description', 
        'store_address', 
        'store_email', 
        'store_phone'
    ];
}
