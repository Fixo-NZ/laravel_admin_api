<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Database;
use Kreait\Firebase\Firestore;
use Kreait\Firebase\Exception\FirebaseException;
use App\Models\Homeowner;
use App\Models\Tradie;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FirebaseService
{
    protected $auth;
    protected $firestore;
    protected $database;

    public function __construct()
    {
        try {
            $factory = (new Factory)
                ->withServiceAccount(config('firebase.credentials'))
                ->withDatabaseUri(config('firebase.database_url'));

            $this->auth = $factory->createAuth();
            $this->firestore = $factory->createFirestore();
            $this->database = $factory->createDatabase();
        } catch (\Exception $e) {
            Log::error('Firebase initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Sync homeowner to Firebase
     */
    public function syncHomeownerToFirebase(Homeowner $homeowner, string $firebaseUid)
    {
        try {
            $userData = [
                'name' => $homeowner->first_name . ' ' . $homeowner->last_name,
                'first_name' => $homeowner->first_name,
                'last_name' => $homeowner->last_name,
                'middle_name' => $homeowner->middle_name,
                'email' => $homeowner->email,
                'phone' => $homeowner->phone,
                'userType' => 'homeowner',
                'laravel_id' => $homeowner->id,
                'address' => $homeowner->address,
                'city' => $homeowner->city,
                'region' => $homeowner->region,
                'postal_code' => $homeowner->postal_code,
                'latitude' => $homeowner->latitude,
                'longitude' => $homeowner->longitude,
                'created_at' => $homeowner->created_at?->toISOString(),
                'updated_at' => now()->toISOString(),
            ];

            // Remove null values
            $userData = array_filter($userData, function($value) {
                return $value !== null;
            });

            $this->firestore->database()
                ->collection('homeowners')
                ->document($firebaseUid)
                ->set($userData);

            Log::info("Homeowner synced to Firebase: {$homeowner->email}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to sync homeowner to Firebase: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sync tradie to Firebase
     */
    public function syncTradieToFirebase(Tradie $tradie, string $firebaseUid)
    {
        try {
            $userData = [
                'name' => $tradie->first_name . ' ' . $tradie->last_name,
                'first_name' => $tradie->first_name,
                'last_name' => $tradie->last_name,
                'middle_name' => $tradie->middle_name,
                'email' => $tradie->email,
                'phone' => $tradie->phone,
                'userType' => 'tradie',
                'laravel_id' => $tradie->id,
                'address' => $tradie->address,
                'city' => $tradie->city,
                'region' => $tradie->region,
                'postal_code' => $tradie->postal_code,
                'latitude' => $tradie->latitude,
                'longitude' => $tradie->longitude,
                'business_name' => $tradie->business_name,
                'license_number' => $tradie->license_number,
                'insurance_details' => $tradie->insurance_details,
                'years_experience' => $tradie->years_experience,
                'hourly_rate' => $tradie->hourly_rate,
                'availability_status' => $tradie->availability_status,
                'service_radius' => $tradie->service_radius,
                'created_at' => $tradie->created_at?->toISOString(),
                'updated_at' => now()->toISOString(),
            ];

            // Remove null values
            $userData = array_filter($userData, function($value) {
                return $value !== null;
            });

            $this->firestore->database()
                ->collection('tradies')
                ->document($firebaseUid)
                ->set($userData);

            Log::info("Tradie synced to Firebase: {$tradie->email}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to sync tradie to Firebase: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify Firebase ID token
     */
    public function verifyIdToken(string $idToken)
    {
        try {
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);
            return $verifiedIdToken->claims()->get('sub'); // Returns Firebase UID
        } catch (\Exception $e) {
            Log::error("Firebase token verification failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user from Firebase by UID
     */
    public function getFirebaseUser(string $uid)
    {
        try {
            return $this->auth->getUser($uid);
        } catch (\Exception $e) {
            Log::error("Failed to get Firebase user: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create Firebase custom token for user
     */
    public function createCustomToken(string $uid, array $claims = [])
    {
        try {
            return $this->auth->createCustomToken($uid, $claims);
        } catch (\Exception $e) {
            Log::error("Failed to create custom token: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete user from Firebase
     */
    public function deleteFirebaseUser(string $uid)
    {
        try {
            $this->auth->deleteUser($uid);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to delete Firebase user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user in Firebase Firestore
     */
    public function updateFirebaseUserData(string $uid, string $userType, array $data)
    {
        try {
            $collection = $userType === 'homeowner' ? 'homeowners' : 'tradies';
            
            $this->firestore->database()
                ->collection($collection)
                ->document($uid)
                ->update($data);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to update Firebase user data: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Save message to both Firebase and MySQL
     */
    public function saveMessage(array $messageData)
    {
        try {
            DB::beginTransaction();

            // Generate Firebase message ID if not provided
            $firebaseMessageId = $messageData['firebase_message_id'] ?? uniqid('msg_', true);
            $firebaseChatId = Chat::generateFirebaseChatId(
                $messageData['sender_firebase_uid'], 
                $messageData['receiver_firebase_uid']
            );

            // Find or create chat in MySQL
            $chat = Chat::firstOrCreate(
                ['firebase_chat_id' => $firebaseChatId],
                [
                    'participant_1_uid' => $messageData['sender_firebase_uid'],
                    'participant_2_uid' => $messageData['receiver_firebase_uid'],
                    'participant_1_type' => $messageData['sender_type'],
                    'participant_2_type' => $messageData['receiver_type'],
                    'participant_1_id' => $this->getLaravelUserId($messageData['sender_firebase_uid'], $messageData['sender_type']),
                    'participant_2_id' => $this->getLaravelUserId($messageData['receiver_firebase_uid'], $messageData['receiver_type']),
                    'is_active' => true,
                ]
            );

            // Save message to MySQL
            $message = Message::create([
                'firebase_message_id' => $firebaseMessageId,
                'firebase_chat_id' => $firebaseChatId,
                'chat_id' => $chat->id,
                'sender_firebase_uid' => $messageData['sender_firebase_uid'],
                'receiver_firebase_uid' => $messageData['receiver_firebase_uid'],
                'sender_id' => $this->getLaravelUserId($messageData['sender_firebase_uid'], $messageData['sender_type']),
                'receiver_id' => $this->getLaravelUserId($messageData['receiver_firebase_uid'], $messageData['receiver_type']),
                'sender_type' => $messageData['sender_type'],
                'receiver_type' => $messageData['receiver_type'],
                'message' => $messageData['message'],
                'is_read' => false,
                'sent_at' => now(),
                'metadata' => $messageData['metadata'] ?? null,
            ]);

            // Update chat with last message info
            $chat->update([
                'last_message' => $messageData['message'],
                'last_sender_uid' => $messageData['sender_firebase_uid'],
                'last_message_at' => now(),
            ]);

            // Save to Firebase
            $firebaseMessageData = [
                'chatId' => $firebaseChatId,
                'senderId' => $messageData['sender_firebase_uid'],
                'receiverId' => $messageData['receiver_firebase_uid'],
                'senderUserType' => $messageData['sender_type'],
                'receiverUserType' => $messageData['receiver_type'],
                'message' => $messageData['message'],
                'timestamp' => now()->toISOString(),
                'read' => false,
            ];

            $this->firestore->database()
                ->collection('messages')
                ->document($firebaseMessageId)
                ->set($firebaseMessageData);

            // Update Firebase chat metadata
            $this->firestore->database()
                ->collection('chats')
                ->document($firebaseChatId)
                ->set([
                    'participants' => [$messageData['sender_firebase_uid'], $messageData['receiver_firebase_uid']],
                    'participantTypes' => [$messageData['sender_type'], $messageData['receiver_type']],
                    'lastMessage' => $messageData['message'],
                    'lastSenderId' => $messageData['sender_firebase_uid'],
                    'lastTimestamp' => now()->toISOString(),
                    'updatedAt' => now()->toISOString(),
                ], ['merge' => true]);

            DB::commit();
            Log::info("Message saved to both MySQL and Firebase: {$firebaseMessageId}");
            
            return [
                'success' => true,
                'message_id' => $message->id,
                'firebase_message_id' => $firebaseMessageId,
                'chat_id' => $chat->id,
                'firebase_chat_id' => $firebaseChatId,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to save message: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Mark messages as read in both Firebase and MySQL
     */
    public function markMessagesAsRead(string $senderUid, string $receiverUid)
    {
        try {
            DB::beginTransaction();

            $firebaseChatId = Chat::generateFirebaseChatId($senderUid, $receiverUid);

            // Mark as read in MySQL
            Message::where('firebase_chat_id', $firebaseChatId)
                ->where('sender_firebase_uid', $senderUid)
                ->where('receiver_firebase_uid', $receiverUid)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            // Mark as read in Firebase
            $messages = $this->firestore->database()
                ->collection('messages')
                ->where('chatId', '=', $firebaseChatId)
                ->where('senderId', '=', $senderUid)
                ->where('receiverId', '=', $receiverUid)
                ->where('read', '=', false)
                ->documents();

            foreach ($messages as $message) {
                $message->reference()->update([
                    ['path' => 'read', 'value' => true]
                ]);
            }

            DB::commit();
            Log::info("Messages marked as read between {$senderUid} and {$receiverUid}");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to mark messages as read: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Laravel user ID by Firebase UID
     */
    private function getLaravelUserId(string $firebaseUid, string $userType): ?int
    {
        if ($userType === 'homeowner') {
            $user = Homeowner::where('firebase_uid', $firebaseUid)->first();
        } else {
            $user = Tradie::where('firebase_uid', $firebaseUid)->first();
        }

        return $user?->id;
    }

    /**
     * Sync Firebase messages to MySQL (for existing messages)
     */
    public function syncFirebaseMessagesToMySQL(string $firebaseChatId)
    {
        try {
            $messages = $this->firestore->database()
                ->collection('messages')
                ->where('chatId', '=', $firebaseChatId)
                ->orderBy('timestamp', 'asc')
                ->documents();

            foreach ($messages as $firebaseMessage) {
                $data = $firebaseMessage->data();
                
                // Check if message already exists in MySQL
                $existingMessage = Message::where('firebase_message_id', $firebaseMessage->id())->first();
                if ($existingMessage) {
                    continue;
                }

                // Create message data for MySQL
                $messageData = [
                    'firebase_message_id' => $firebaseMessage->id(),
                    'sender_firebase_uid' => $data['senderId'],
                    'receiver_firebase_uid' => $data['receiverId'],
                    'sender_type' => $data['senderUserType'],
                    'receiver_type' => $data['receiverUserType'],
                    'message' => $data['message'],
                ];

                $this->saveMessage($messageData);
            }

            Log::info("Synced Firebase messages to MySQL for chat: {$firebaseChatId}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to sync Firebase messages to MySQL: " . $e->getMessage());
            return false;
        }
    }
}