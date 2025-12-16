<?php

// Simple API test script
$baseUrl = 'http://localhost:8000/api';

// Test homeowner creation
$homeownerData = [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john' . time() . '@example.com',
    'firebase_uid' => 'test-homeowner-uid-' . time(),
    'password' => 'password123'
];

echo "Testing Homeowner Creation...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/homeowners');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($homeownerData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

// Test tradie creation
$tradieData = [
    'first_name' => 'Jane',
    'last_name' => 'Smith',
    'middle_name' => 'M',
    'email' => 'jane' . time() . '@example.com',
    'firebase_uid' => 'test-tradie-uid-' . time(),
    'password' => 'password123'
];

echo "Testing Tradie Creation...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/tradies');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($tradieData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

// Test getting homeowners
echo "Testing Get Homeowners...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/homeowners');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";