<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatParticipant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Get all chats for the authenticated user.
     */
    public function index(Request $request)
    {
        $chats = $request->user()
            ->chats()
            ->with(['participants', 'latestMessage.sender'])
            ->withCount(['messages as unread_count' => function ($query) use ($request) {
                $query->where('sender_id', '!=', $request->user()->user_id)
                      ->where('is_read', false);
            }])
            ->latest('updated_at')
            ->get();

        // Add display names and avatars for each chat
        $chats->transform(function ($chat) use ($request) {
            $chat->display_name = $chat->getDisplayName($request->user()->user_id);
            $chat->display_avatar = $chat->getDisplayAvatar($request->user()->user_id);
            return $chat;
        });

        return response()->json($chats);
    }

    /**
     * Create a new chat.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'chat_type' => 'required|in:one_to_one,group',
            'chat_name' => 'nullable|required_if:chat_type,group|string|max:255',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'required|exists:users,user_id',
        ]);

        // For one-to-one chats, check if chat already exists
        if ($validated['chat_type'] === 'one_to_one') {
            if (count($validated['participant_ids']) !== 1) {
                return response()->json([
                    'message' => 'One-to-one chat must have exactly one other participant',
                ], 400);
            }

            $otherUserId = $validated['participant_ids'][0];
            
            // Check if one-to-one chat already exists
            $existingChat = $this->findOneToOneChat($request->user()->user_id, $otherUserId);
            
            if ($existingChat) {
                return response()->json([
                    'message' => 'Chat already exists',
                    'data' => $existingChat->load(['participants', 'latestMessage']),
                ], 200);
            }
        }

        DB::beginTransaction();
        try {
            // Create chat
            $chat = Chat::create([
                'chat_type' => $validated['chat_type'],
                'chat_name' => $validated['chat_name'] ?? null,
                'created_by' => $request->user()->user_id,
            ]);

            // Add creator as owner
            ChatParticipant::create([
                'chat_id' => $chat->chat_id,
                'user_id' => $request->user()->user_id,
                'role' => $validated['chat_type'] === 'group' ? 'owner' : 'member',
            ]);

            // Add other participants
            foreach ($validated['participant_ids'] as $userId) {
                if ($userId !== $request->user()->user_id) {
                    ChatParticipant::create([
                        'chat_id' => $chat->chat_id,
                        'user_id' => $userId,
                        'role' => 'member',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Chat created successfully',
                'data' => $chat->load(['participants', 'creator']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to create chat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific chat.
     */
    public function show(Request $request, Chat $chat)
    {
        // Verify user is participant
        $this->authorizeParticipant($request, $chat);

        $chat->load(['participants', 'creator', 'chatParticipants.user']);

        return response()->json($chat);
    }

    /**
     * Update a chat.
     */
    public function update(Request $request, Chat $chat)
    {
        // Verify user is participant with admin privileges
        $this->authorizeAdmin($request, $chat);

        $validated = $request->validate([
            'chat_name' => 'nullable|string|max:255',
        ]);

        $chat->update($validated);

        return response()->json([
            'message' => 'Chat updated successfully',
            'data' => $chat,
        ]);
    }

    /**
     * Delete a chat.
     */
    public function destroy(Request $request, Chat $chat)
    {
        // Only owner can delete
        $participant = $chat->chatParticipants()
            ->where('user_id', $request->user()->user_id)
            ->first();

        if (!$participant || !$participant->isOwner()) {
            return response()->json([
                'message' => 'Only the chat owner can delete the chat',
            ], 403);
        }

        $chat->delete();

        return response()->json([
            'message' => 'Chat deleted successfully',
        ]);
    }

    /**
     * Get chat participants.
     */
    public function participants(Request $request, Chat $chat)
    {
        $this->authorizeParticipant($request, $chat);

        $participants = $chat->participants()
            ->withPivot('participant_id', 'role', 'joined_at')
            ->get();

        return response()->json($participants);
    }

    /**
     * Add a participant to the chat.
     */
    public function addParticipant(Request $request, Chat $chat)
    {
        // Only group chats can add participants
        if (!$chat->isGroup()) {
            return response()->json([
                'message' => 'Cannot add participants to one-to-one chat',
            ], 400);
        }

        // Verify user has admin privileges
        $this->authorizeAdmin($request, $chat);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,user_id',
        ]);

        // Check if user is already a participant
        $exists = $chat->participants()
            ->where('user_id', $validated['user_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'User is already a participant',
            ], 400);
        }

        $participant = $chat->addParticipant($validated['user_id']);

        return response()->json([
            'message' => 'Participant added successfully',
            'data' => $participant->load('user'),
        ], 201);
    }

    /**
     * Remove a participant from the chat.
     */
    public function removeParticipant(Request $request, Chat $chat, User $user)
    {
        // Verify user has admin privileges
        $this->authorizeAdmin($request, $chat);

        // Cannot remove owner
        $targetParticipant = $chat->chatParticipants()
            ->where('user_id', $user->user_id)
            ->first();

        if ($targetParticipant && $targetParticipant->isOwner()) {
            return response()->json([
                'message' => 'Cannot remove the chat owner',
            ], 400);
        }

        $removed = $chat->removeParticipant($user->user_id);

        if (!$removed) {
            return response()->json([
                'message' => 'User is not a participant in this chat',
            ], 404);
        }

        return response()->json([
            'message' => 'Participant removed successfully',
        ]);
    }

    /**
     * Update participant role.
     */
    public function updateParticipantRole(Request $request, Chat $chat, User $user)
    {
        // Only owner can change roles
        $requesterParticipant = $chat->chatParticipants()
            ->where('user_id', $request->user()->user_id)
            ->first();

        if (!$requesterParticipant || !$requesterParticipant->isOwner()) {
            return response()->json([
                'message' => 'Only the chat owner can change participant roles',
            ], 403);
        }

        $validated = $request->validate([
            'role' => 'required|in:member,admin,owner',
        ]);

        $participant = $chat->chatParticipants()
            ->where('user_id', $user->user_id)
            ->first();

        if (!$participant) {
            return response()->json([
                'message' => 'User is not a participant in this chat',
            ], 404);
        }

        $participant->changeRole($validated['role']);

        return response()->json([
            'message' => 'Participant role updated successfully',
            'data' => $participant->fresh(['user']),
        ]);
    }

    /**
     * Mark all messages in chat as read.
     */
    public function markAsRead(Request $request, Chat $chat)
    {
        $this->authorizeParticipant($request, $chat);

        $chat->markAsReadForUser($request->user()->user_id);

        return response()->json([
            'message' => 'All messages marked as read',
        ]);
    }

    /**
     * Find existing one-to-one chat between two users.
     */
    protected function findOneToOneChat(string $userId1, string $userId2): ?Chat
    {
        return Chat::where('chat_type', 'one_to_one')
            ->whereHas('participants', function ($query) use ($userId1) {
                $query->where('user_id', $userId1);
            })
            ->whereHas('participants', function ($query) use ($userId2) {
                $query->where('user_id', $userId2);
            })
            ->first();
    }

    /**
     * Verify user is a participant in the chat.
     */
    protected function authorizeParticipant(Request $request, Chat $chat): void
    {
        $isParticipant = $chat->participants()
            ->where('user_id', $request->user()->user_id)
            ->exists();

        if (!$isParticipant) {
            abort(403, 'You are not a participant in this chat');
        }
    }

    /**
     * Verify user has admin privileges in the chat.
     */
    protected function authorizeAdmin(Request $request, Chat $chat): void
    {
        $participant = $chat->chatParticipants()
            ->where('user_id', $request->user()->user_id)
            ->first();

        if (!$participant || !$participant->hasAdminPrivileges()) {
            abort(403, 'You do not have admin privileges in this chat');
        }
    }
}