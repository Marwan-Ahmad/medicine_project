<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreHouseMedicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'scientificname',
        'commercialname',
        'category',
        'company',
        'quntity',
        'expirationdate',
        'price'
    ];
}
