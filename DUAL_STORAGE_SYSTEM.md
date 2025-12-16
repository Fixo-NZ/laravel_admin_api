# Dual Storage System Documentation

## Overview
This system saves all users, messages, and chat data to **both MySQL and Firebase** simultaneously, providing:
- **MySQL**: Advanced querying, reporting, analytics, and admin management
- **Firebase**: Real-time chat functionality for Flutter apps
- **Auto-sync**: Keeps both systems synchronized

## Architecture

### Data Flow
```
Flutter App → Firebase Auth → Laravel API → MySQL + Firebase
                                    ↓
                            Both systems updated
```

### Storage Strategy
- **Users**: Stored in both MySQL tables and Firebase collections
- **Messages**: Saved to both MySQL `messages` table and Firebase `messages` collection
- **Chats**: Tracked in both MySQL `chats` table and Firebase `chats` collection

## Database Schema

### MySQL Tables

#### `chats` Table
```sql
- id (primary key)
- firebase_chat_id (unique)
- participant_1_uid (Firebase UID)
- participant_2_uid (Firebase UID)
- participant_1_type (homeowner/tradie)
- participant_2_type (homeowner/tradie)
- participant_1_id (Laravel ID)
- participant_2_id (Laravel ID)
- last_message
- last_sender_uid
- last_message_at
- is_active
- created_at, updated_at
```

#### `messages` Table
```sql
- id (primary key)
- firebase_message_id (unique)
- firebase_chat_id
- chat_id (foreign key to chats)
- sender_firebase_uid
- receiver_firebase_uid
- sender_id (Laravel ID)
- receiver_id (Laravel ID)
- sender_type (homeowner/tradie)
- receiver_type (homeowner/tradie)
- message (text)
- is_read (boolean)
- sent_at
- read_at
- metadata (JSON)
- created_at, updated_at
```

### Firebase Collections

#### `homeowners/{firebase_uid}`
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "userType": "homeowner",
  "laravel_id": 123,
  "created_at": "timestamp",
  "updated_at": "timestamp"
}
```

#### `tradies/{firebase_uid}`
```json
{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "userType": "tradie",
  "tradeType": "Plumber",
  "laravel_id": 456,
  "created_at": "timestamp",
  "updated_at": "timestamp"
}
```

#### `messages/{message_id}`
```json
{
  "chatId": "uid1-uid2",
  "senderId": "firebase_uid",
  "receiverId": "firebase_uid",
  "senderUserType": "homeowner",
  "receiverUserType": "tradie",
  "message": "Hello!",
  "timestamp": "2025-01-21T10:00:00Z",
  "read": false
}
```

#### `chats/{chat_id}`
```json
{
  "participants": ["uid1", "uid2"],
  "participantTypes": ["homeowner", "tradie"],
  "lastMessage": "Hello!",
  "lastSenderId": "firebase_uid",
  "lastTimestamp": "2025-01-21T10:00:00Z",
  "updatedAt": "2025-01-21T10:00:00Z"
}
```

## API Endpoints

### Chat Management
- `GET /api/chats/user-chats?firebase_uid={uid}` - Get user's chats
- `GET /api/chats/{chat_id}/messages` - Get chat messages
- `POST /api/chats/send-message` - Send message (saves to both systems)
- `POST /api/chats/mark-as-read` - Mark messages as read
- `GET /api/chats/stats?firebase_uid={uid}` - Get chat statistics
- `POST /api/chats/search-messages` - Search messages
- `POST /api/chats/sync-firebase` - Sync Firebase messages to MySQL

### User Management
- `POST /api/homeowners` - Create homeowner (auto-syncs to Firebase)
- `POST /api/tradies` - Create tradie (auto-syncs to Firebase)
- `PUT /api/homeowners/{id}` - Update homeowner (auto-syncs to Firebase)
- `PUT /api/tradies/{id}` - Update tradie (auto-syncs to Firebase)

### Firebase Integration
- `POST /api/firebase/sync-homeowner` - Manual sync homeowner
- `POST /api/firebase/sync-tradie` - Manual sync tradie
- `POST /api/firebase/verify-token` - Verify Firebase token

## Usage Examples

### Send Message (Saves to Both Systems)
```bash
curl -X POST http://localhost:8000/api/chats/send-message \
  -H "Content-Type: application/json" \
  -d '{
    "sender_firebase_uid": "homeowner_uid_123",
    "receiver_firebase_uid": "tradie_uid_456",
    "sender_type": "homeowner",
    "receiver_type": "tradie",
    "message": "Hello, I need a plumber!"
  }'
```

### Get User Chats
```bash
curl -X GET "http://localhost:8000/api/chats/user-chats?firebase_uid=homeowner_uid_123"
```

### Create User (Auto-syncs to Firebase)
```bash
curl -X POST http://localhost:8000/api/homeowners \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "firebase_uid": "homeowner_uid_123"
  }'
```

## Benefits

### MySQL Advantages
- **Complex Queries**: JOIN operations, aggregations, reporting
- **Data Integrity**: Foreign keys, constraints, transactions
- **Analytics**: Business intelligence, user behavior analysis
- **Admin Panel**: Easy management through Laravel admin
- **Backup & Recovery**: Traditional database backup solutions
- **Performance**: Optimized for complex queries and large datasets

### Firebase Advantages
- **Real-time**: Live message updates in Flutter apps
- **Offline Support**: Works when device is offline
- **Scalability**: Handles concurrent users efficiently
- **Security Rules**: Fine-grained access control
- **Mobile Optimized**: Perfect for Flutter integration

### Dual Storage Benefits
- **Redundancy**: Data safety with two storage systems
- **Best of Both**: Real-time features + advanced querying
- **Flexibility**: Use the right tool for each task
- **Migration Safety**: Easy to switch between systems if needed

## Synchronization

### Automatic Sync
The system automatically syncs data when:
- New users are created
- User profiles are updated
- Messages are sent
- Messages are marked as read

### Manual Sync
You can manually sync data using:
- `POST /api/firebase/sync-homeowner`
- `POST /api/firebase/sync-tradie`
- `POST /api/chats/sync-firebase`

### Sync Monitoring
All sync operations are logged for monitoring:
- Successful syncs
- Failed operations
- Data inconsistencies
- Performance metrics

## Error Handling

### Graceful Degradation
- If Firebase fails, data is still saved to MySQL
- If MySQL fails, transaction is rolled back
- Retry mechanisms for temporary failures
- Detailed error logging for debugging

### Data Consistency
- Database transactions ensure data integrity
- Rollback on partial failures
- Validation before saving to both systems
- Conflict resolution strategies

## Performance Optimization

### Database Indexes
- Firebase UID indexes for fast lookups
- Message timestamp indexes for chronological queries
- Chat participant indexes for user chat lists
- Composite indexes for complex queries

### Caching Strategy
- Laravel model caching for frequently accessed data
- Firebase real-time listeners for live updates
- Query result caching for expensive operations
- CDN for static assets

## Monitoring & Analytics

### Available Metrics
- Total messages sent/received per user
- Chat activity patterns
- User engagement statistics
- System performance metrics
- Error rates and types

### Reporting Capabilities
- Daily/weekly/monthly chat reports
- User activity dashboards
- Message volume analytics
- Popular conversation topics
- Response time analysis

This dual storage system provides the perfect balance of real-time functionality and advanced data management capabilities!