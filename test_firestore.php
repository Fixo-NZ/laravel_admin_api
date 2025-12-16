<?php

require __DIR__ . '/vendor/autoload.php';

echo "Testing Firebase Realtime Database connection...\n\n";

try {
    $factory = (new \Kreait\Firebase\Factory)
        ->withServiceAccount(__DIR__ . '/storage/app/firebase-credentials.json')
        ->withDatabaseUri('https://fixo-chat-default-rtdb.asia-southeast1.firebasedatabase.app');
    
    $database = $factory->createDatabase();
    
    echo "✅ SUCCESS: Firebase Realtime Database connected!\n";
    echo "✅ Database instance created!\n\n";
    
    // Test writing data
    echo "Testing write operation...\n";
    $database->getReference('test/connection_test')->set([
        'message' => 'Connection test from Laravel',
        'timestamp' => ['.sv' => 'timestamp'],
        'success' => true
    ]);
    
    echo "✅ Test data written successfully!\n";
    echo "✅ Check Firebase Console → Realtime Database → 'test' node\n\n";
    
    // Test reading data
    echo "Testing read operation...\n";
    $data = $database->getReference('test/connection_test')->getValue();
    echo "✅ Data read successfully:\n";
    print_r($data);
    
    echo "\n🎉 All tests passed! Firebase is working correctly.\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
