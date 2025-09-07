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
    protected $casts = [
        'is_read' => 'boolean',
        'sent_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($message) {
            if (!$message->sent_at) {
                $message->sent_at = now();
            }
        });
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    public function sender()
    {
         return $this->belongsTo(User::class, 'sender_id', 'user_id');
    }
}