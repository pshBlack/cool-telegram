<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Message extends Model implements HasMedia
{
    use HasFactory, HasUlids, SoftDeletes, InteractsWithMedia;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'message_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'chat_id',
        'sender_id',
        'content',
        'type',
        'sent_at',
        'is_read',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'is_read' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set sent_at when creating a message
        static::creating(function ($message) {
            if (!$message->sent_at) {
                $message->sent_at = now();
            }
        });
    }

    /**
     * Get the chat that this message belongs to.
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class, 'chat_id', 'chat_id');
    }

    /**
     * Get the user who sent this message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id', 'user_id');
    }

    /**
     * Scope a query to only include unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to only include read messages.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope a query to only include text messages.
     */
    public function scopeText($query)
    {
        return $query->where('type', 'text');
    }

    /**
     * Scope a query to filter by message type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Mark this message as read.
     */
    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true]);
    }

    /**
     * Mark this message as unread.
     */
    public function markAsUnread(): bool
    {
        return $this->update(['is_read' => false]);
    }

    /**
     * Check if the message is from a specific user.
     */
    public function isFrom(string $userId): bool
    {
        return $this->sender_id === $userId;
    }

    /**
     * Check if the message is a text message.
     */
    public function isText(): bool
    {
        return $this->type === 'text';
    }

    /**
     * Get formatted sent time (e.g., "2 hours ago").
     */
    public function getFormattedSentTimeAttribute(): string
    {
        return $this->sent_at->diffForHumans();
    }

    /**
     * Get message preview (truncated content).
     */
    public function getPreviewAttribute(): string
    {
        if ($this->type !== 'text') {
            return ucfirst($this->type);
        }

        return \Illuminate\Support\Str::limit($this->content ?? '', 50);
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');

        $this->addMediaCollection('images');

        $this->addMediaCollection('videos');

        $this->addMediaCollection('documents');

        $this->addMediaCollection('audio');
    }

    /**
     * Check if message has attachments.
     */
    public function hasAttachments(): bool
    {
        return $this->getMedia('attachments')->isNotEmpty() ||
               $this->getMedia('images')->isNotEmpty() ||
               $this->getMedia('videos')->isNotEmpty() ||
               $this->getMedia('documents')->isNotEmpty() ||
               $this->getMedia('audio')->isNotEmpty();
    }

    /**
     * Get all attachments regardless of collection.
     */
    public function getAllAttachments()
    {
        return $this->getMedia('*');
    }

    /**
     * Check if message is an image.
     */
    public function isImage(): bool
    {
        return $this->type === 'image' || $this->getMedia('images')->isNotEmpty();
    }

    /**
     * Check if message is a video.
     */
    public function isVideo(): bool
    {
        return $this->type === 'video' || $this->getMedia('videos')->isNotEmpty();
    }

    /**
     * Check if message is a document.
     */
    public function isDocument(): bool
    {
        return $this->type === 'document' || $this->getMedia('documents')->isNotEmpty();
    }

    /**
     * Check if message is audio.
     */
    public function isAudio(): bool
    {
        return $this->type === 'audio' || $this->getMedia('audio')->isNotEmpty();
    }
}