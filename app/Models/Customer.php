<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $guarded = []; // Tidak ada atribut yang diblokir

    public function order()
    {
        return $this->hasMany(Order::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }
}
