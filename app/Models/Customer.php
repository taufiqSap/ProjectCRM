<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
    public function getLastServiceDateAttribute()
    {
        $date = DB::table('orders')
            ->where('customer_id', $this->id)
            ->max('date');

        return $date ? \Carbon\Carbon::parse($date)->format('d M Y') : null;
    }

}
