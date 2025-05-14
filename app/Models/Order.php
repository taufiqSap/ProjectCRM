<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }
    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class);
    }
    public function orderPart()
    {
        return $this->belongsTo(OrderPart::class);
    }

}
