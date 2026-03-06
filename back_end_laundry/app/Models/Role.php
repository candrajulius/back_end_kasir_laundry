<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'roles_name',
    ];
    
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
