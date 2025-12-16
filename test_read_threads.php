<?php

require __DIR__.'/vendor/autoload.php';

use Kreait\Firebase\Factory;

$credentialsPath = __DIR__.'/storage/app/firebase-credentials.json';
$databaseUrl = 'https://fixo-chat-default-rtdb.asia-southeast1.firebasedatabase.app';

$factory = (new Factory)
    ->withServiceAccount($credentialsPath)
    ->withDatabaseUri($databaseUrl);

$database = $factory->createDatabase();

echo "📖 Reading all threads from Firebase...\n\n";

$threads = $database->getReference('threads')->getValue();

if ($threads) {
    foreach ($threads as $threadId => $threadData) {
        echo "Thread ID: $threadId\n";
        echo "  Tradie ID: " . ($threadData['tradie_id'] ?? 'N/A') . "\n";
        echo "  Homeowner ID: " . ($threadData['homeowner_id'] ?? 'N/A') . "\n";
        echo "  Last Message: " . ($threadData['last_message'] ?? 'N/A') . "\n";
        
        if (isset($threadData['messages'])) {
            echo "  Messages:\n";
            foreach ($threadData['messages'] as $msgId => $msgData) {
                echo "    - $msgId:\n";
                echo "      sender_id: " . ($msgData['sender_id'] ?? 'N/A') . "\n";
                echo "      content: " . ($msgData['content'] ?? 'N/A') . "\n";
                echo "      date: " . ($msgData['date'] ?? 'N/A') . "\n";
            }
        } else {
            echo "  No messages\n";
        }
        echo "\n";
    }
} else {
    echo "❌ No threads found\n";
}
