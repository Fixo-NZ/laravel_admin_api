<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Illuminate\Support\Facades\Log;

class FirestoreChatService
{
    protected $database;

    public function __construct()
    {
        $credentialsPath = storage_path('app/firebase-credentials.json');
        
        if (!file_exists($credentialsPath)) {
            throw new \Exception('Firebase credentials file not found');
        }

        $factory = (new Factory)
            ->withServiceAccount($credentialsPath)
            ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));
        
        $this->database = $factory->createDatabase();
    }

    /**
     * Send message to Firebase Realtime Database
     * Laravel writes, Flutter reads
     */
    public function sendMessage(array $data)
    {
        try {
            $senderId = $data['sender_id'];
            $receiverId = $data['receiver_id'];
            $senderType = $data['sender_type']; // 'homeowner' or 'tradie'
            $receiverType = $data['receiver_type'];
            $message = $data['message'];
            $replyTo = $data['reply_to'] ?? null; // Optional reply data

            // Determine tradie and homeowner IDs
            $tradieId = $senderType === 'tradie' ? $senderId : $receiverId;
            $homeownerId = $senderType === 'homeowner' ? $senderId : $receiverId;

            // Find or create thread
            $threadId = $this->findOrCreateThread($tradieId, $homeownerId);

            // Get next message ID
            $messageId = $this->getNextMessageId($threadId);

            // Prepare message data
            $messageData = [
                'sender_id' => $senderId,
                'sender_type' => $senderType,
                'content' => $message,
                'date' => ['.sv' => 'timestamp'],
                'read' => false,
                'message_id' => $messageId,
            ];

            // Add reply data if present
            if ($replyTo) {
                $messageData['reply_to'] = $replyTo;
            }

            // Add message to Firebase Realtime Database
            $this->database
                ->getReference("threads/$threadId/messages/msg_$messageId")
                ->set($messageData);

            // Update thread last message
            $this->database
                ->getReference("threads/$threadId")
                ->update([
                    'last_message' => $message,
                    'last_message_time' => ['.sv' => 'timestamp'],
                    'updated_at' => ['.sv' => 'timestamp'],
                ]);

            Log::info("Message sent to Firebase", [
                'thread_id' => $threadId,
                'message_id' => $messageId,
            ]);

            return [
                'success' => true,
                'thread_id' => $threadId,
                'message_id' => $messageId,
            ];

        } catch (\Exception $e) {
            Log::error('Firebase send message error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Find or create thread between tradie and homeowner
     */
    protected function findOrCreateThread($tradieId, $homeownerId)
    {
        // Ensure we have valid IDs (not 0 or null)
        if (empty($tradieId) || empty($homeownerId) || $tradieId == 0 || $homeownerId == 0) {
            Log::error("Invalid user IDs for thread", [
                'tradie_id' => $tradieId,
                'homeowner_id' => $homeownerId,
            ]);
            throw new \Exception("Invalid user IDs: tradie_id=$tradieId, homeowner_id=$homeownerId");
        }

        // Get all threads and search for matching one
        $threads = $this->database->getReference('threads')->getValue();

        if ($threads) {
            foreach ($threads as $threadKey => $threadData) {
                // Check if both tradie_id and homeowner_id match
                // This ensures the same thread is used regardless of who initiates
                if (isset($threadData['tradie_id']) && 
                    isset($threadData['homeowner_id']) &&
                    $threadData['tradie_id'] == $tradieId && 
                    $threadData['homeowner_id'] == $homeownerId) {
                    
                    Log::info("Found existing thread", [
                        'thread_key' => $threadKey,
                        'tradie_id' => $tradieId,
                        'homeowner_id' => $homeownerId,
                    ]);
                    
                    return $threadKey;
                }
            }
        }

        // Create new thread only if no match found
        $threadId = $this->getNextThreadId();
        $threadDocName = "threadID_$threadId";

        $this->database->getReference("threads/$threadDocName")->set([
            'thread_id' => (int)$threadId,
            'sender_1' => (int)$tradieId,
            'sender_2' => (int)$homeownerId,
            'tradie_id' => (int)$tradieId,
            'homeowner_id' => (int)$homeownerId,
            'created_at' => ['.sv' => 'timestamp'],
            'updated_at' => ['.sv' => 'timestamp'],
            'last_message' => '',
            'last_message_time' => ['.sv' => 'timestamp'],
            'is_archived' => false,
            'is_deleted' => false,
            'message_count' => 0,
        ]);

        Log::info("Created new thread", [
            'thread_id' => $threadId,
            'thread_doc_name' => $threadDocName,
            'tradie_id' => $tradieId,
            'homeowner_id' => $homeownerId,
        ]);

        return $threadDocName;
    }

    /**
     * Get next thread ID (sequential)
     */
    protected function getNextThreadId()
    {
        $counterRef = $this->database->getReference('counters/thread_counter');
        $currentId = $counterRef->getValue()['current_id'] ?? 0;
        $nextId = $currentId + 1;
        
        $counterRef->set(['current_id' => $nextId]);
        return $nextId;
    }

    /**
     * Get next message ID for a thread
     */
    protected function getNextMessageId($threadId)
    {
        $messages = $this->database->getReference("threads/$threadId/messages")->getValue();

        $highestId = 0;
        if ($messages) {
            foreach ($messages as $msgKey => $msgData) {
                if (str_starts_with($msgKey, 'msg_')) {
                    $id = (int) substr($msgKey, 4);
                    if ($id > $highestId) {
                        $highestId = $id;
                    }
                }
            }
        }

        return $highestId + 1;
    }

    /**
     * Create or get chat room
     */
    public function createRoom(array $data)
    {
        try {
            $tradieId = $data['tradie_id'];
            $homeownerId = $data['homeowner_id'];

            $threadId = $this->findOrCreateThread($tradieId, $homeownerId);

            return [
                'success' => true,
                'room_id' => $threadId,
            ];

        } catch (\Exception $e) {
            Log::error('Firebase create room error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Block user in Firebase
     */
    public function blockUser($blockerId, $blockedUserId)
    {
        try {
            $profileRef = $this->database->getReference("userProfiles/$blockerId");
            $profile = $profileRef->getValue();

            $blockedUsers = $profile['blockedUsers'] ?? [];
            if (!in_array($blockedUserId, $blockedUsers)) {
                $blockedUsers[] = $blockedUserId;
            }
            
            $profileRef->update([
                'blockedUsers' => $blockedUsers,
                'updatedAt' => ['.sv' => 'timestamp'],
            ]);

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('Firebase block user error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Unblock user in Firebase
     */
    public function unblockUser($blockerId, $blockedUserId)
    {
        try {
            $profileRef = $this->database->getReference("userProfiles/$blockerId");
            $profile = $profileRef->getValue();

            $blockedUsers = $profile['blockedUsers'] ?? [];
            $blockedUsers = array_values(array_diff($blockedUsers, [$blockedUserId]));
            
            $profileRef->update([
                'blockedUsers' => $blockedUsers,
                'updatedAt' => ['.sv' => 'timestamp'],
            ]);

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('Firebase unblock user error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Mark messages as read
     */
    public function markMessagesAsRead($threadId, $userId)
    {
        try {
            $messages = $this->database->getReference("threads/$threadId/messages")->getValue();

            if ($messages) {
                foreach ($messages as $msgKey => $msgData) {
                    if (isset($msgData['sender_id']) && $msgData['sender_id'] != $userId && !($msgData['read'] ?? false)) {
                        $this->database->getReference("threads/$threadId/messages/$msgKey")->update([
                            'read' => true,
                        ]);
                    }
                }
            }

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('Firebase mark as read error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
