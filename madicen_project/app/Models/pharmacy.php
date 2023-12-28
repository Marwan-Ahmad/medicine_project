<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\AuthenticatableTrait;

class pharmacy extends Model

{
    use HasApiTokens, HasFactory, Notifiable;
    use HasFactory;
    protected $fillable = [
        'firstname', 'lastname', 'address', 'phone', 'password'
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [

        'password' => 'hashed',
    ];

    public function orders()
    {
        return $this->hasMany(cart::class, "pharmesist_id", "id");
    }
}
