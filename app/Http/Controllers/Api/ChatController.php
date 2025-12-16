<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Get all chats for a user
     */
    public function getUserChats(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'firebase_uid' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $chats = Chat::forUser($request->firebase_uid)
                ->active()
                ->with(['latestMessage'])
                ->orderBy('last_message_at', 'desc')
                ->get();

            // Add participant info and unread count
            $chatsWithDetails = $chats->map(function ($chat) use ($request) {
                $otherParticipant = $chat->getOtherParticipant($request->firebase_uid);
                $unreadCount = $chat->getUnreadCountForUser($request->firebase_uid);

                return [
                    'id' => $chat->id,
                    'firebase_chat_id' => $chat->firebase_chat_id,
                    'other_participant' => $otherParticipant,
                    'last_message' => $chat->last_message,
                    'last_message_at' => $chat->last_message_at,
                    'unread_count' => $unreadCount,
                    'latest_message' => $chat->latestMessage,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $chatsWithDetails
            ]);

        } catch (\Exception $e) {
            Log::error('Get user chats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get messages for a specific chat
     */
    public function getChatMessages(Request $request, $chatId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $chat = Chat::findOrFail($chatId);
            $perPage = $request->get('per_page', 50);

            $messages = Message::forChat($chatId)
                ->orderBy('sent_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'chat' => $chat,
                    'messages' => $messages
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get chat messages error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Chat not found or internal server error'
            ], 404);
        }
    }

    /**
     * Send a message (Laravel writes to Firestore, Flutter reads)
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required|integer',
            'receiver_id' => 'required|integer',
            'sender_type' => 'required|in:homeowner,tradie',
            'receiver_type' => 'required|in:homeowner,tradie',
            'message' => 'required|string|max:5000',
            'reply_to' => 'nullable|array',
            'reply_to.message_id' => 'nullable|string',
            'reply_to.sender_name' => 'nullable|string',
            'reply_to.content' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Use new Firestore service
            $firestoreService = app(\App\Services\FirestoreChatService::class);
            
            $messageData = [
                'sender_id' => $request->sender_id,
                'receiver_id' => $request->receiver_id,
                'sender_type' => $request->sender_type,
                'receiver_type' => $request->receiver_type,
                'message' => $request->message,
            ];

            // Add reply data if present
            if ($request->has('reply_to')) {
                $messageData['reply_to'] = $request->reply_to;
            }

            $result = $firestoreService->sendMessage($messageData);

            if ($result['success']) {
                // Also save to MySQL for backup/analytics
                try {
                    Message::create([
                        'firebase_thread_id' => $result['thread_id'] ?? null,
                        'firebase_message_id' => isset($result['message_id']) ? 'msg_' . $result['message_id'] : null,
                        'sender_id' => $request->sender_id,
                        'receiver_id' => $request->receiver_id,
                        'sender_type' => $request->sender_type,
                        'receiver_type' => $request->receiver_type,
                        'message' => $request->message,
                        'sent_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    // Log but don't fail the request if MySQL save fails
                    Log::warning('Failed to save message to MySQL: ' . $e->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'data' => $result
                ], 201);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $result['error'] ?? 'Unknown error'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Send message error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sender_firebase_uid' => 'required|string',
            'receiver_firebase_uid' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $success = $this->firebaseService->markMessagesAsRead(
                $request->sender_firebase_uid,
                $request->receiver_firebase_uid
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Messages marked as read'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark messages as read'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Mark as read error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get chat statistics
     */
    public function getChatStats(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'firebase_uid' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $firebaseUid = $request->firebase_uid;

            $stats = [
                'total_chats' => Chat::forUser($firebaseUid)->active()->count(),
                'total_messages_sent' => Message::where('sender_firebase_uid', $firebaseUid)->count(),
                'total_messages_received' => Message::where('receiver_firebase_uid', $firebaseUid)->count(),
                'unread_messages' => Message::where('receiver_firebase_uid', $firebaseUid)->unread()->count(),
                'active_chats_today' => Chat::forUser($firebaseUid)
                    ->active()
                    ->whereDate('last_message_at', today())
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Get chat stats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Search messages
     */
    public function searchMessages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'firebase_uid' => 'required|string',
            'query' => 'required|string|min:2',
            'chat_id' => 'nullable|exists:chats,id',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = Message::forUser($request->firebase_uid)
                ->where('message', 'LIKE', '%' . $request->query . '%');

            if ($request->chat_id) {
                $query->where('chat_id', $request->chat_id);
            }

            $perPage = $request->get('per_page', 20);
            $messages = $query->with(['chat'])
                ->orderBy('sent_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $messages
            ]);

        } catch (\Exception $e) {
            Log::error('Search messages error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Sync Firebase messages to MySQL
     */
    public function syncFirebaseMessages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'firebase_chat_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $success = $this->firebaseService->syncFirebaseMessagesToMySQL($request->firebase_chat_id);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Firebase messages synced to MySQL successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync Firebase messages'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Sync Firebase messages error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Create or get chat room
     */
    public function createRoom(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tradie_id' => 'required|integer',
            'homeowner_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $firestoreService = app(\App\Services\FirestoreChatService::class);
            
            $result = $firestoreService->createRoom([
                'tradie_id' => $request->tradie_id,
                'homeowner_id' => $request->homeowner_id,
            ]);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create room'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Create room error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Block user
     */
    public function blockUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'blocker_id' => 'required|integer',
            'blocked_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $firestoreService = app(\App\Services\FirestoreChatService::class);
            
            $result = $firestoreService->blockUser(
                $request->blocker_id,
                $request->blocked_id
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'User blocked successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to block user'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Block user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Unblock user
     */
    public function unblockUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'blocker_id' => 'required|integer',
            'blocked_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $firestoreService = app(\App\Services\FirestoreChatService::class);
            
            $result = $firestoreService->unblockUser(
                $request->blocker_id,
                $request->blocked_id
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'User unblocked successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to unblock user'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Unblock user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}