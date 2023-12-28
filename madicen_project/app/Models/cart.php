<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cart extends Model
{
    use HasFactory;
    protected $fillable = [
        'status',
        'paymentStatus',
        'pharmesist_id'
    ];

    public function medicins()
    {
        return $this->belongsToMany(StoreHouseMedicine::class, 'cart_medicins', 'cart_id', 'medicin_id')->withPivot('cart_id', 'id', 'medicin_id', 'quantity');
    }
}
