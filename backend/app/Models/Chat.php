<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Chat extends Model implements HasMedia
{
    use HasFactory, HasUlids, InteractsWithMedia;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'chat_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'chat_type',
        'chat_name',
        'chat_avatar_url',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user who created this chat.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Get the messages in this chat.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'chat_id', 'chat_id');
    }

    /**
     * Get the participants in this chat.
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_participants', 'chat_id', 'user_id')
            ->withPivot('participant_id', 'joined_at', 'role')
            ->withTimestamps();
    }

    /**
     * Get the chat participants records.
     */
    public function chatParticipants(): HasMany
    {
        return $this->hasMany(ChatParticipant::class, 'chat_id', 'chat_id');
    }

    /**
     * Get the latest message in the chat.
     */
    public function latestMessage(): HasMany
    {
        return $this->messages()->one()->latestOfMany();
    }

    /**
     * Scope a query to only include group chats.
     */
    public function scopeGroup($query)
    {
        return $query->where('chat_type', 'group');
    }

    /**
     * Scope a query to only include one-to-one chats.
     */
    public function scopeOneToOne($query)
    {
        return $query->where('chat_type', 'one_to_one');
    }

    /**
     * Check if chat is a group chat.
     */
    public function isGroup(): bool
    {
        return $this->chat_type === 'group';
    }

    /**
     * Check if chat is a one-to-one chat.
     */
    public function isOneToOne(): bool
    {
        return $this->chat_type === 'one_to_one';
    }

    /**
     * Get unread messages count for a specific user.
     */
    public function unreadMessagesCount(string $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Mark all messages as read for a specific user.
     */
    public function markAsReadForUser(string $userId): void
    {
        $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Add a participant to the chat.
     */
    public function addParticipant(string $userId, string $role = 'member'): ChatParticipant
    {
        return ChatParticipant::create([
            'chat_id' => $this->chat_id,
            'user_id' => $userId,
            'role' => $role,
        ]);
    }

    /**
     * Remove a participant from the chat.
     */
    public function removeParticipant(string $userId): bool
    {
        return ChatParticipant::where('chat_id', $this->chat_id)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Get chat display name for a specific user (useful for one-to-one chats).
     */
    public function getDisplayName(?string $currentUserId = null): string
    {
        if ($this->isGroup()) {
            return $this->chat_name ?? 'Group Chat';
        }

        // For one-to-one chats, return the other participant's name
        if ($currentUserId) {
            $otherParticipant = $this->participants()
                ->where('user_id', '!=', $currentUserId)
                ->first();

            return $otherParticipant?->full_name ?? 'Unknown User';
        }

        return $this->chat_name ?? 'Chat';
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('chat_avatar')
            ->singleFile()
            ->useFallbackUrl('/images/default-group-avatar.png')
            ->useFallbackPath(public_path('/images/default-group-avatar.png'));
    }

    /**
     * Get the chat's avatar URL.
     */
    public function getChatAvatarAttribute(): ?string
    {
        // For one-to-one chats, we might want to show the other user's avatar
        // This is handled in the application layer
        
        // First check if there's a media library avatar
        $media = $this->getFirstMedia('chat_avatar');
        if ($media) {
            return $media->getUrl();
        }

        // Fall back to chat_avatar_url field
        return $this->chat_avatar_url;
    }

    /**
     * Get display avatar for a specific user (for one-to-one chats).
     */
    public function getDisplayAvatar(?string $currentUserId = null): ?string
    {
        if ($this->isGroup()) {
            return $this->chat_avatar;
        }

        // For one-to-one chats, return the other participant's avatar
        if ($currentUserId) {
            $otherParticipant = $this->participants()
                ->where('user_id', '!=', $currentUserId)
                ->first();

            return $otherParticipant?->avatar;
        }

        return $this->chat_avatar;
    }
}