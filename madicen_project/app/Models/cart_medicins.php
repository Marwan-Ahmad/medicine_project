<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cart_medicins extends Model
{
    use HasFactory;
    protected $table = "cart_medicins";
    protected $fillable = ['cart_id', 'id', 'medicin_id', 'quantity'];
}
