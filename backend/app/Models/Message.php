<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $primaryKey = 'message_id';
    public $timestamps = false; 

    protected $fillable = [
        'chat_id', 'sender_id', 'content', 'sent_at', 'is_read'
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}