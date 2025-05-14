<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPart extends Model
{
    use HasFactory;

    public function order()
    {
        return $this->hasMany(Order::class);
    }

    public function part ()
    {
        return $this->belongsTo(Part::class);
    }
}
