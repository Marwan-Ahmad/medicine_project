<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class storehouse extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'phone', 'password'
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [

        'password' => 'hashed',
    ];
}
