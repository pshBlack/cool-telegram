<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatParticipant extends Model
{
    use HasFactory, HasUlids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chat_participants';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'participant_id';

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
        'user_id',
        'role',
        'joined_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set joined_at when creating a participant
        static::creating(function ($participant) {
            if (!$participant->joined_at) {
                $participant->joined_at = now();
            }
        });
    }

    /**
     * Get the chat that this participant belongs to.
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class, 'chat_id', 'chat_id');
    }

    /**
     * Get the user for this participant.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Scope a query to only include members.
     */
    public function scopeMembers($query)
    {
        return $query->where('role', 'member');
    }

    /**
     * Scope a query to only include admins.
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope a query to only include owners.
     */
    public function scopeOwners($query)
    {
        return $query->where('role', 'owner');
    }

    /**
     * Check if the participant is a member.
     */
    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    /**
     * Check if the participant is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the participant is the owner.
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Check if the participant has admin privileges (admin or owner).
     */
    public function hasAdminPrivileges(): bool
    {
        return in_array($this->role, ['admin', 'owner']);
    }

    /**
     * Promote the participant to admin.
     */
    public function promoteToAdmin(): bool
    {
        return $this->update(['role' => 'admin']);
    }

    /**
     * Demote the participant to member.
     */
    public function demoteToMember(): bool
    {
        return $this->update(['role' => 'member']);
    }

    /**
     * Change the participant's role.
     */
    public function changeRole(string $role): bool
    {
        if (!in_array($role, ['member', 'admin', 'owner'])) {
            return false;
        }

        return $this->update(['role' => $role]);
    }

    /**
     * Get formatted joined time.
     */
    public function getFormattedJoinedTimeAttribute(): string
    {
        return $this->joined_at->diffForHumans();
    }
}