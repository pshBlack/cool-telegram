<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;
    
    public function getAuthPassword()
{
    return $this->password_hash;
}


    protected $primaryKey = 'user_id'; // Custom primary key

    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'email',
        'password_hash',
        'google_id',
        'avatar_url',
        'bio',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];
}
