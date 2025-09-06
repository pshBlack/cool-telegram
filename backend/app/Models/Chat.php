<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{

     protected $primaryKey = 'chat_id';
            protected $keyType = 'int'; 


   protected $fillable = [
        'chat_type',
        'chat_name',
        'chat_avatar_url',
        'created_by',
    ];

   public function users()
{
    return $this->belongsToMany(User::class, 'chat_participants', 'chat_id', 'user_id')
                ->withPivot('role', 'joined_at');
}   

public function messages()
{
    return $this->hasMany(Message::class, 'chat_id');
}

}
