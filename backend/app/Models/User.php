<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory;
    protected $primaryKey = 'user_id'; // Custom primary key
    
    public function getAuthPassword()
{
    return $this->password_hash;
}

public function chats()
{
    return $this->belongsToMany(Chat::class, 'chat_participants', 'user_id', 'chat_id')
                ->withPivot('role', 'joined_at');
}



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
