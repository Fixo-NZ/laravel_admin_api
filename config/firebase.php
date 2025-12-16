<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Credentials
    |--------------------------------------------------------------------------
    |
    | Path to the Firebase service account JSON file.
    | You can download this from Firebase Console > Project Settings > Service Accounts
    |
    */
    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),

    /*
    |--------------------------------------------------------------------------
    | Firebase Database URL
    |--------------------------------------------------------------------------
    |
    | The URL of your Firebase Realtime Database.
    | Format: https://your-project-id-default-rtdb.region.firebasedatabase.app/
    |
    */
    'database_url' => env('FIREBASE_DATABASE_URL', 'https://fixo-chat-default-rtdb.asia-southeast1.firebasedatabase.app'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Project ID
    |--------------------------------------------------------------------------
    |
    | Your Firebase project ID
    |
    */
    'project_id' => env('FIREBASE_PROJECT_ID', 'fixo-chat'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Web API Key
    |--------------------------------------------------------------------------
    |
    | Your Firebase Web API Key (for client-side operations)
    |
    */
    'api_key' => env('FIREBASE_API_KEY', 'AIzaSyBE9TiOIKlMpvpYF18JdrPs99XBleK_m1Q'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Auth Domain
    |--------------------------------------------------------------------------
    |
    | Your Firebase Auth Domain
    |
    */
    'auth_domain' => env('FIREBASE_AUTH_DOMAIN', 'fixo-chat.firebaseapp.com'),

    /*
    |--------------------------------------------------------------------------
    | Auto Sync Settings
    |--------------------------------------------------------------------------
    |
    | Configure automatic synchronization between Laravel and Firebase
    |
    */
    'auto_sync' => [
        'enabled' => env('FIREBASE_AUTO_SYNC', true),
        'on_create' => env('FIREBASE_SYNC_ON_CREATE', true),
        'on_update' => env('FIREBASE_SYNC_ON_UPDATE', true),
        'on_delete' => env('FIREBASE_SYNC_ON_DELETE', true),
    ],
];