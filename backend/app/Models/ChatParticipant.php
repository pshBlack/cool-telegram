<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatParticipant extends Model
{
    protected $primaryKey = 'participant_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'chat_id',
        'user_id',
        'role',
        'joined_at',
    ];

    protected static function booted()
    {
        static::creating(function ($participant) {
            if (!$participant->participant_id) {
                $participant->participant_id = (string) Str::uuid();
            }
            if (!$participant->joined_at) {
                $participant->joined_at = now();
            }
        });
    }
}
