# Laravel-Firebase Integration Documentation

## Overview
This Laravel API is integrated with Firebase to provide a hybrid backend system where:
- **Laravel + MySQL**: Manages main application data (users, jobs, bookings, etc.)
- **Firebase**: Handles real-time chat and authentication
- **Auto-sync**: Keeps user data synchronized between both systems

## Setup Instructions

### 1. Install Dependencies
```bash
composer install
```

### 2. Firebase Service Account Setup
1. Go to [Firebase Console](https://console.firebase.google.com)
2. Select your `fixo-chat` project
3. Go to Project Settings → Service Accounts
4. Click "Generate new private key"
5. Save the JSON file as `storage/app/firebase-credentials.json`

### 3. Environment Configuration
The `.env` file already includes Firebase configuration:
```env
FIREBASE_PROJECT_ID=fixo-chat
FIREBASE_DATABASE_URL=https://fixo-chat-default-rtdb.asia-southeast1.firebasedatabase.app
FIREBASE_API_KEY=AIzaSyBE9TiOIKlMpvpYF18JdrPs99XBleK_m1Q
FIREBASE_AUTH_DOMAIN=fixo-chat.firebaseapp.com
FIREBASE_CREDENTIALS=storage/app/firebase-credentials.json
FIREBASE_AUTO_SYNC=true
```

### 4. Run Migrations
```bash
php artisan migrate
```

## API Endpoints

### Firebase Integration
- `POST /api/firebase/sync-homeowner` - Sync homeowner to Firebase
- `POST /api/firebase/sync-tradie` - Sync tradie to Firebase
- `POST /api/firebase/verify-token` - Verify Firebase ID token
- `GET /api/firebase/user/{firebase_uid}` - Get user by Firebase UID

### Homeowner Management
- `GET /api/homeowners` - List all homeowners
- `POST /api/homeowners` - Create homeowner
- `GET /api/homeowners/{id}` - Get specific homeowner
- `PUT /api/homeowners/{id}` - Update homeowner
- `DELETE /api/homeowners/{id}` - Delete homeowner
- `GET /api/homeowners/firebase/{firebase_uid}` - Get homeowner by Firebase UID

### Tradie Management
- `GET /api/tradies` - List all tradies
- `POST /api/tradies` - Create tradie
- `GET /api/tradies/{id}` - Get specific tradie
- `PUT /api/tradies/{id}` - Update tradie
- `DELETE /api/tradies/{id}` - Delete tradie
- `GET /api/tradies/firebase/{firebase_uid}` - Get tradie by Firebase UID
- `POST /api/tradies/search` - Search tradies by location/service

## Auto-Sync Features

### Automatic Synchronization
When enabled in config, the system automatically:
- **On Create**: Syncs new users to Firebase
- **On Update**: Updates Firebase when Laravel data changes
- **On Delete**: Removes users from Firebase when deleted from Laravel

### Manual Sync
You can manually sync users using the Firebase endpoints:

```bash
# Sync homeowner
curl -X POST http://localhost:8000/api/firebase/sync-homeowner \
  -H "Content-Type: application/json" \
  -d '{"homeowner_id": 1, "firebase_uid": "firebase-uid-here"}'

# Sync tradie
curl -X POST http://localhost:8000/api/firebase/sync-tradie \
  -H "Content-Type: application/json" \
  -d '{"tradie_id": 1, "firebase_uid": "firebase-uid-here"}'
```

## Data Flow

### User Registration Flow
1. **Flutter App**: User registers via Firebase Auth
2. **Firebase**: Creates user account and returns UID
3. **Flutter App**: Calls Laravel API with user data + Firebase UID
4. **Laravel**: Stores user in MySQL with `firebase_uid` field
5. **Laravel**: Auto-syncs user data back to Firestore (if enabled)

### User Authentication Flow
1. **Flutter App**: User logs in via Firebase Auth
2. **Firebase**: Returns ID token
3. **Flutter App**: Sends ID token to Laravel API
4. **Laravel**: Verifies token and returns user data from MySQL

### Chat Integration
1. **Laravel**: Manages user profiles and main app data
2. **Firebase**: Handles real-time chat between users
3. **Sync**: User data stays synchronized between both systems

## Database Schema

### Added Fields
Both `homeowners` and `tradies` tables now include:
- `firebase_uid` (string, nullable, unique) - Links to Firebase user

### Firebase Collections
- `homeowners/{firebase_uid}` - Homeowner chat profiles
- `tradies/{firebase_uid}` - Tradie chat profiles
- `messages/{messageId}` - Chat messages
- `chats/{chatId}` - Chat metadata

## Configuration

### Firebase Config (`config/firebase.php`)
```php
'credentials' => env('FIREBASE_CREDENTIALS'),
'database_url' => env('FIREBASE_DATABASE_URL'),
'project_id' => env('FIREBASE_PROJECT_ID'),
'auto_sync' => [
    'enabled' => env('FIREBASE_AUTO_SYNC', true),
    'on_create' => env('FIREBASE_SYNC_ON_CREATE', true),
    'on_update' => env('FIREBASE_SYNC_ON_UPDATE', true),
    'on_delete' => env('FIREBASE_SYNC_ON_DELETE', true),
],
```

## Services

### FirebaseService
Main service class handling:
- User synchronization to Firebase
- Token verification
- Firebase user management
- Firestore operations

### Usage Example
```php
use App\Services\FirebaseService;

$firebaseService = new FirebaseService();

// Sync homeowner to Firebase
$success = $firebaseService->syncHomeownerToFirebase($homeowner, $firebaseUid);

// Verify Firebase token
$uid = $firebaseService->verifyIdToken($idToken);
```

## Security

### Firebase Authentication
- Uses Firebase Admin SDK for server-side verification
- Validates ID tokens from Flutter apps
- Maintains secure communication between systems

### API Protection
- Firebase middleware available for protecting routes
- Token verification ensures authenticated requests
- User data isolation between homeowners and tradies

## Monitoring & Logging

### Logs
All Firebase operations are logged:
- Successful syncs
- Failed operations
- Token verification attempts
- Error details

### Error Handling
- Graceful fallbacks if Firebase is unavailable
- Detailed error messages for debugging
- Automatic retry mechanisms where appropriate

## Testing

### API Testing
```bash
# Test homeowner creation with Firebase sync
curl -X POST http://localhost:8000/api/homeowners \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "firebase_uid": "test-firebase-uid"
  }'
```

### Firebase Token Testing
```bash
# Verify Firebase token
curl -X POST http://localhost:8000/api/firebase/verify-token \
  -H "Content-Type: application/json" \
  -d '{"id_token": "firebase-id-token-here"}'
```

This integration provides a robust hybrid system combining Laravel's powerful backend capabilities with Firebase's real-time features!