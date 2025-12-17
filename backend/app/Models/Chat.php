<?php

namespace App\Models;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{

     protected $primaryKey = 'chat_id';
            public $incrementing = false;  
            protected $keyType = 'string';
            protected $appends = ['display_name'];


   protected $fillable = [
        'chat_type',
        'chat_name',
        'chat_avatar_url',
        'created_by',
    ];



    
    protected static function booted()
    {
        static::creating(function ($chat) {
            if (!$chat->chat_id) {
                $chat->chat_id = (string) Str::uuid();
            }
        });
    }
    

     public function getDisplayNameAttribute()
    {
        $authUser = request()->user();

        if ($this->chat_type === 'one_to_one') {
        $otherUser = $this->users->firstWhere('user_id', '!=', $authUser->user_id);
        return $otherUser ? $otherUser->username : 'Unknown';
    }

        return $this->name ?? 'Group Chat';
    }

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
