<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Test database connection
    $pdo = DB::connection()->getPdo();
    echo "✅ Database connection: SUCCESS\n";
    
    // Test if tables exist
    $tables = ['homeowners', 'tradies', 'chats', 'messages'];
    foreach ($tables as $table) {
        $result = DB::select("SHOW TABLES LIKE '$table'");
        if (count($result) > 0) {
            echo "✅ Table '$table': EXISTS\n";
        } else {
            echo "❌ Table '$table': NOT FOUND\n";
        }
    }
    
    // Test creating a homeowner
    echo "\nTesting homeowner creation...\n";
    $homeowner = DB::table('homeowners')->insert([
        'firebase_uid' => 'test-uid-' . time(),
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john' . time() . '@example.com',
        'password' => bcrypt('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    if ($homeowner) {
        echo "✅ Homeowner creation: SUCCESS\n";
    } else {
        echo "❌ Homeowner creation: FAILED\n";
    }
    
    // Count records
    $homeownerCount = DB::table('homeowners')->count();
    $tradieCount = DB::table('tradies')->count();
    echo "\n📊 Current records:\n";
    echo "   Homeowners: $homeownerCount\n";
    echo "   Tradies: $tradieCount\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}