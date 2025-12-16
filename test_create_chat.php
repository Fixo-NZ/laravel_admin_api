<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Homeowner;
use App\Models\Tradie;
use App\Services\FirestoreChatService;

try {
    echo "Creating test chat...\n\n";
    
    // Get first homeowner and tradie
    $homeowner = Homeowner::first();
    $tradie = Tradie::first();
    
    if (!$homeowner) {
        echo "❌ No homeowner found. Please register a homeowner first.\n";
        exit;
    }
    
    if (!$tradie) {
        echo "❌ No tradie found. Please register a tradie first.\n";
        exit;
    }
    
    echo "Homeowner: {$homeowner->first_name} {$homeowner->last_name} (ID: {$homeowner->id})\n";
    echo "Tradie: {$tradie->first_name} {$tradie->last_name} (ID: {$tradie->id})\n\n";
    
    // Create chat ID
    $chatId = "homeowner_{$homeowner->id}_tradie_{$tradie->id}";
    echo "Chat ID: $chatId\n\n";
    
    // Initialize Firebase service
    $chatService = new FirestoreChatService();
    
    // Send a test message
    $result = $chatService->sendMessage(
        chatId: $chatId,
        senderId: (string)$homeowner->id,
        senderType: 'homeowner',
        receiverId: (string)$tradie->id,
        receiverType: 'tradie',
        message: 'Hello! I need help with a plumbing issue.'
    );
    
    if ($result) {
        echo "✅ Test chat created successfully!\n";
        echo "Chat ID: $chatId\n";
        echo "Message sent from homeowner to tradie\n\n";
        echo "Now open the apps and you should see the chat in the Messages screen!\n";
    } else {
        echo "❌ Failed to create chat\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
