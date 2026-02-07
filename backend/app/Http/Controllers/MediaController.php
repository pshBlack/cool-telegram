<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MediaController extends Controller
{
    /**
     * Upload user avatar.
     */
    public function uploadUserAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Delete old avatar if exists
        $user->clearMediaCollection('avatar');

        // Add new avatar
        $media = $user->addMediaFromRequest('avatar')
            ->toMediaCollection('avatar');

        return response()->json([
            'message' => 'Avatar uploaded successfully',
            'avatar_url' => $media->getUrl(),
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Delete user avatar.
     */
    public function deleteUserAvatar(Request $request)
    {
        $user = $request->user();
        $user->clearMediaCollection('avatar');

        // Also clear avatar_url if it was from Google
        $user->update(['avatar_url' => null]);

        return response()->json([
            'message' => 'Avatar deleted successfully',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Upload chat avatar (group chat).
     */
    public function uploadChatAvatar(Request $request, Chat $chat)
    {
        // Check if user is admin or owner
        $participant = $chat->chatParticipants()
            ->where('user_id', $request->user()->user_id)
            ->first();

        if (!$participant || !$participant->hasAdminPrivileges()) {
            return response()->json([
                'message' => 'You do not have permission to update chat avatar',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Delete old avatar
        $chat->clearMediaCollection('chat_avatar');

        // Add new avatar
        $media = $chat->addMediaFromRequest('avatar')
            ->toMediaCollection('chat_avatar');

        return response()->json([
            'message' => 'Chat avatar uploaded successfully',
            'chat_avatar_url' => $media->getUrl(),
            'chat' => $chat->fresh(),
        ]);
    }

    /**
     * Upload message attachment.
     */
    public function uploadMessageAttachment(Request $request, Chat $chat, Message $message)
    {
        // Verify user is participant
        $isParticipant = $chat->participants()
            ->where('user_id', $request->user()->user_id)
            ->exists();

        if (!$isParticipant) {
            return response()->json([
                'message' => 'You are not a participant in this chat',
            ], 403);
        }

        // Verify message belongs to user
        if ($message->sender_id !== $request->user()->user_id) {
            return response()->json([
                'message' => 'You can only add attachments to your own messages',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // 10MB
            'type' => 'required|in:image,video,document,audio,attachment',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $type = $request->input('type');
        
        // Additional validation based on type
        if ($type === 'image') {
            $request->validate([
                'file' => 'mimes:jpeg,png,jpg,gif,webp',
            ]);
        } elseif ($type === 'video') {
            $request->validate([
                'file' => 'mimes:mp4,mov,avi,wmv',
            ]);
        } elseif ($type === 'audio') {
            $request->validate([
                'file' => 'mimes:mp3,wav,ogg,m4a',
            ]);
        } elseif ($type === 'document') {
            $request->validate([
                'file' => 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt',
            ]);
        }

        // Determine collection based on type
        $collection = $type === 'attachment' ? 'attachments' : $type . 's';

        // Add file to message
        $media = $message->addMediaFromRequest('file')
            ->toMediaCollection($collection);

        // Update message type if it's text
        if ($message->type === 'text') {
            $message->update(['type' => $type]);
        }

        return response()->json([
            'message' => 'Attachment uploaded successfully',
            'media' => [
                'id' => $media->id,
                'name' => $media->file_name,
                'url' => $media->getUrl(),
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'collection' => $media->collection_name,
            ],
            'message' => $message->fresh()->load('media'),
        ]);
    }

    /**
     * Delete message attachment.
     */
    public function deleteMessageAttachment(Request $request, Chat $chat, Message $message, int $mediaId)
    {
        // Verify message belongs to user
        if ($message->sender_id !== $request->user()->user_id) {
            return response()->json([
                'message' => 'You can only delete attachments from your own messages',
            ], 403);
        }

        $media = $message->media()->find($mediaId);

        if (!$media) {
            return response()->json([
                'message' => 'Attachment not found',
            ], 404);
        }

        $media->delete();

        return response()->json([
            'message' => 'Attachment deleted successfully',
            'message' => $message->fresh()->load('media'),
        ]);
    }

    /**
     * Upload multiple message attachments.
     */
    public function uploadMultipleAttachments(Request $request, Chat $chat, Message $message)
    {
        $validator = Validator::make($request->all(), [
            'files.*' => 'required|file|max:10240',
            'type' => 'required|in:image,video,document,audio,attachment',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $type = $request->input('type');
        $collection = $type === 'attachment' ? 'attachments' : $type . 's';
        $uploadedMedia = [];

        foreach ($request->file('files') as $file) {
            $media = $message->addMedia($file)
                ->toMediaCollection($collection);

            $uploadedMedia[] = [
                'id' => $media->id,
                'name' => $media->file_name,
                'url' => $media->getUrl(),
                'mime_type' => $media->mime_type,
                'size' => $media->size,
            ];
        }

        // Update message type if needed
        if ($message->type === 'text') {
            $message->update(['type' => $type]);
        }

        return response()->json([
            'message' => 'Attachments uploaded successfully',
            'media' => $uploadedMedia,
            'message' => $message->fresh()->load('media'),
        ]);
    }
}