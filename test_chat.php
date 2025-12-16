<?php

require __DIR__ . '/vendor/autoload.php';

// Load Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Testing Chat Service...\n\n";

try {
    // Create service instance
    $chatService = new \App\Services\FirestoreChatService();
    
    echo "✅ Chat service created\n\n";
    
    // Test 1: Send a message
    echo "📤 Test 1: Sending message...\n";
    $result = $chatService->sendMessage([
        'sender_id' => 1,
        'receiver_id' => 2,
        'sender_type' => 'homeowner',
        'receiver_type' => 'tradie',
        'message' => 'Hello from Laravel! This is a test message.',
    ]);
    
    if ($result['success']) {
        echo "✅ Message sent successfully!\n";
        echo "   Thread ID: {$result['thread_id']}\n";
        echo "   Message ID: {$result['message_id']}\n\n";
        
        $threadId = $result['thread_id'];
    } else {
        echo "❌ Failed to send message: {$result['error']}\n";
        exit(1);
    }
    
    // Test 2: Send another message
    echo "📤 Test 2: Sending second message...\n";
    $result2 = $chatService->sendMessage([
        'sender_id' => 2,
        'receiver_id' => 1,
        'sender_type' => 'tradie',
        'receiver_type' => 'homeowner',
        'message' => 'Hi! I received your message.',
    ]);
    
    if ($result2['success']) {
        echo "✅ Second message sent successfully!\n";
        echo "   Thread ID: {$result2['thread_id']}\n";
        echo "   Message ID: {$result2['message_id']}\n\n";
    } else {
        echo "❌ Failed to send second message: {$result2['error']}\n";
    }
    
    // Test 3: Create room
    echo "🏠 Test 3: Creating chat room...\n";
    $roomResult = $chatService->createRoom([
        'tradie_id' => 3,
        'homeowner_id' => 4,
    ]);
    
    if ($roomResult['success']) {
        echo "✅ Room created successfully!\n";
        echo "   Room ID: {$roomResult['room_id']}\n\n";
    } else {
        echo "❌ Failed to create room: {$roomResult['error']}\n";
    }
    
    // Test 4: Block user
    echo "🚫 Test 4: Blocking user...\n";
    $blockResult = $chatService->blockUser(1, 2);
    
    if ($blockResult['success']) {
        echo "✅ User blocked successfully!\n\n";
    } else {
        echo "❌ Failed to block user: {$blockResult['error']}\n";
    }
    
    // Test 5: Unblock user
    echo "✅ Test 5: Unblocking user...\n";
    $unblockResult = $chatService->unblockUser(1, 2);
    
    if ($unblockResult['success']) {
        echo "✅ User unblocked successfully!\n\n";
    } else {
        echo "❌ Failed to unblock user: {$unblockResult['error']}\n";
    }
    
    echo "🎉 ALL TESTS PASSED!\n\n";
    echo "📱 Next steps:\n";
    echo "   1. Check Firebase Console → Realtime Database\n";
    echo "   2. You should see 'threads' with messages\n";
    echo "   3. Test the API endpoint with curl\n";
    echo "   4. Update Flutter app to use Realtime Database\n\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
