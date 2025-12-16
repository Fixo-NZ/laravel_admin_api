<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Homeowner;
use Illuminate\Support\Facades\Hash;

try {
    echo "Testing Homeowner Registration...\n";
    
    $homeowner = Homeowner::create([
        'name' => 'Test User',
        'email' => 'test' . time() . '@example.com',
        'password' => Hash::make('password123'),
        'phone' => '1234567890',
        'status' => 'active',
    ]);
    
    echo "✅ Success! Homeowner created with ID: " . $homeowner->id . "\n";
    echo "Name: " . $homeowner->name . "\n";
    echo "Email: " . $homeowner->email . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
