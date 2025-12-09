# Review & Rating System - Database Setup Documentation

## Overview
Your Laravel API is fully configured to store submitted rates and reviews from the Flutter frontend to the database. The system includes:

- **Reviews Table**: Stores all reviews with detailed ratings
- **ReviewReports Table**: Allows users to report inappropriate reviews
- **Complete API Endpoints**: For submitting, retrieving, and managing reviews
- **Relationship Models**: Proper associations between Reviews, Jobs, Homeowners, and Tradies

---

## Database Schema

### 1. Reviews Table
**Location**: `database/migrations/2025_11_02_001425_create_reviews_table.php`

**Columns**:
```
- id (BigInt, Primary Key)
- job_id (Foreign Key → jobs)
- homeowner_id (Foreign Key → homeowners) - Customer who left review
- tradie_id (Foreign Key → tradies) - Service provider being reviewed
- rating (TinyInt 1-5) - Overall rating
- feedback (Text) - Review text/comment
- service_quality_rating (TinyInt 1-5) - Optional detailed rating
- service_quality_comment (Text) - Optional comment
- performance_rating (TinyInt 1-5) - Optional detailed rating
- performance_comment (Text) - Optional comment
- contractor_service_rating (TinyInt 1-5) - Optional detailed rating
- response_time_rating (TinyInt 1-5) - Optional detailed rating
- best_feature (String 255) - Best feature mentioned
- images (JSON) - Array of image URLs
- show_username (Boolean, default: true)
- helpful_count (Integer, default: 0)
- status (Enum) - 'pending', 'approved', 'reported', 'hidden' (default: 'approved')
- created_at (Timestamp)
- updated_at (Timestamp)
```

**Indexes**:
- `tradie_id`
- `homeowner_id`
- `job_id`
- `rating`
- `status`
- **Unique**: `[job_id, homeowner_id]` - One review per job per homeowner

### 2. ReviewReports Table
**Location**: `database/migrations/2025_11_02_001438_create_review_reports_table.php`

**Columns**:
```
- id (BigInt, Primary Key)
- review_id (Foreign Key → reviews, onDelete: cascade)
- reporter_type (String) - 'App\Models\Homeowner' or 'App\Models\Tradie'
- reporter_id (BigInt) - ID of the reporter
- reason (Enum) - 'spam', 'offensive', 'inappropriate', 'fake', 'other'
- description (Text, nullable) - Detailed reason for report
- status (Enum) - 'pending', 'reviewed', 'resolved', 'dismissed' (default: 'pending')
- admin_notes (Text, nullable) - Admin notes on the report
- created_at (Timestamp)
- updated_at (Timestamp)
```

**Unique Constraint**: `[review_id, reporter_type, reporter_id]` - One report per review per user

---

## Models

### Review Model
**Location**: `app/Models/Review.php`

**Fillable Fields**:
```php
'job_id', 'homeowner_id', 'tradie_id', 'rating', 'feedback',
'service_quality_rating', 'service_quality_comment', 'performance_rating',
'performance_comment', 'contractor_service_rating', 'response_time_rating',
'best_feature', 'images', 'show_username', 'helpful_count', 'status'
```

**Relationships**:
- `job()` - BelongsTo Job
- `homeowner()` - BelongsTo Homeowner
- `tradie()` - BelongsTo Tradie
- `reports()` - HasMany ReviewReport

**Scopes**:
- `approved()` - Filter approved reviews
- `forTradie($tradieId)` - Filter by tradie
- `forHomeowner($homeownerId)` - Filter by homeowner

**Static Methods**:
- `getTradieAverageRating($tradieId)` - Returns average rating (1-5)
- `getTradieReviewCount($tradieId)` - Returns total review count
- `getTradieRatingBreakdown($tradieId)` - Returns count by star rating

---

## API Endpoints

### Base URL
```
https://your-api-domain/api/reviews
```

### 1. Submit a Review (Store)
**Endpoint**: `POST /api/reviews`  
**Authentication**: Required (Sanctum)  
**User Role**: Homeowner

**Request Body**:
```json
{
  "job_id": 123,
  "tradie_id": 456,
  "rating": 5,
  "feedback": "Excellent work, very professional!",
  "service_quality_rating": 5,
  "service_quality_comment": "High quality service",
  "performance_rating": 4,
  "performance_comment": "On time delivery",
  "contractor_service_rating": 5,
  "response_time_rating": 5,
  "best_feature": "Professionalism",
  "show_username": true
}
```

**Validation Rules**:
```
job_id - required, exists in jobs table
tradie_id - required, exists in tradies table
rating - required, integer, min:1, max:5
feedback - nullable, string, max:5000
service_quality_rating - nullable, integer, min:1, max:5
service_quality_comment - nullable, string, max:1000
performance_rating - nullable, integer, min:1, max:5
performance_comment - nullable, string, max:1000
contractor_service_rating - nullable, integer, min:1, max:5
response_time_rating - nullable, integer, min:1, max:5
best_feature - nullable, string, max:255
show_username - boolean
```

**Response (201 Created)**:
```json
{
  "success": true,
  "message": "Review submitted successfully",
  "data": {
    "id": 1,
    "job_id": 123,
    "homeowner_id": 789,
    "tradie_id": 456,
    "rating": 5,
    "feedback": "Excellent work, very professional!",
    "status": "approved",
    "created_at": "2025-11-24T10:30:00Z",
    "updated_at": "2025-11-24T10:30:00Z",
    "homeowner": { "id": 789, "name": "John Doe" },
    "tradie": { "id": 456, "name": "Jane Smith" }
  }
}
```

**Error Response (422 Validation Failed)**:
```json
{
  "success": false,
  "errors": {
    "job_id": ["The job_id field is required."],
    "rating": ["The rating must be between 1 and 5."]
  }
}
```

**Error Response (403 Forbidden)**:
```json
{
  "success": false,
  "message": "You have already reviewed this job"
}
```

---

### 2. Check if Job Can Be Reviewed
**Endpoint**: `GET /api/reviews/can-review/{jobId}`  
**Authentication**: Required (Sanctum)

**Response (200 OK)**:
```json
{
  "success": true,
  "can_review": true,
  "job": {
    "id": 123,
    "title": "Kitchen Renovation",
    "status": "completed"
  }
}
```

**Response (200 OK - Already Reviewed)**:
```json
{
  "success": false,
  "can_review": false,
  "message": "You have already reviewed this job"
}
```

---

### 3. Get Tradie Reviews
**Endpoint**: `GET /api/reviews/tradie/{tradieId}`  
**Authentication**: Optional (public route)

**Query Parameters**:
```
page - integer, default 1 (pagination)
```

**Response (200 OK)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "job_id": 123,
      "homeowner_id": 789,
      "tradie_id": 456,
      "rating": 5,
      "feedback": "Excellent work!",
      "status": "approved",
      "show_username": true,
      "helpful_count": 3,
      "created_at": "2025-11-24T10:30:00Z"
    }
  ],
  "stats": {
    "average_rating": 4.8,
    "total_reviews": 10,
    "rating_breakdown": {
      "5": 8,
      "4": 2,
      "3": 0,
      "2": 0,
      "1": 0
    }
  }
}
```

---

### 4. Get Tradie Statistics
**Endpoint**: `GET /api/reviews/tradie/{tradieId}/stats`  
**Authentication**: Optional (public route)

**Response (200 OK)**:
```json
{
  "success": true,
  "data": {
    "total_reviews": 10,
    "average_rating": 4.8,
    "rating_breakdown": {
      "5": {
        "count": 8,
        "percentage": 80.0
      },
      "4": {
        "count": 2,
        "percentage": 20.0
      },
      "3": {
        "count": 0,
        "percentage": 0.0
      }
    },
    "detailed_ratings": {
      "service_quality": 4.9,
      "performance": 4.8,
      "contractor_service": 4.7,
      "response_time": 4.9
    }
  }
}
```

---

### 5. Get Job Review
**Endpoint**: `GET /api/reviews/job/{jobId}`  
**Authentication**: Optional (public route)

**Response (200 OK)**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "job_id": 123,
    "homeowner_id": 789,
    "tradie_id": 456,
    "rating": 5,
    "feedback": "Excellent work!",
    "status": "approved",
    "created_at": "2025-11-24T10:30:00Z",
    "homeowner": { "id": 789, "name": "John Doe" },
    "tradie": { "id": 456, "name": "Jane Smith" }
  }
}
```

---

### 6. Mark Review as Helpful
**Endpoint**: `POST /api/reviews/{reviewId}/helpful`  
**Authentication**: Required (Sanctum)

**Response (200 OK)**:
```json
{
  "success": true,
  "message": "Review marked as helpful",
  "helpful_count": 4
}
```

---

### 7. Report a Review
**Endpoint**: `POST /api/reviews/{reviewId}/report`  
**Authentication**: Required (Sanctum)

**Request Body**:
```json
{
  "reason": "offensive",
  "description": "This review contains offensive language"
}
```

**Validation Rules**:
```
reason - required, in: 'spam','offensive','inappropriate','fake','other'
description - nullable, string, max:1000
```

**Response (201 Created)**:
```json
{
  "success": true,
  "message": "Review reported successfully. Our team will review it shortly.",
  "data": {
    "id": 5,
    "review_id": 1,
    "reporter_type": "App\\Models\\Homeowner",
    "reporter_id": 789,
    "reason": "offensive",
    "status": "pending",
    "created_at": "2025-11-24T10:35:00Z"
  }
}
```

---

### 8. Get My Reviews
**Endpoint**: `GET /api/reviews/my-reviews`  
**Authentication**: Required (Sanctum)  
**User Role**: Homeowner

**Query Parameters**:
```
page - integer, default 1 (pagination)
```

**Response (200 OK)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "job_id": 123,
      "tradie_id": 456,
      "rating": 5,
      "feedback": "Great work!",
      "status": "approved",
      "created_at": "2025-11-24T10:30:00Z",
      "tradie": { "id": 456, "name": "Jane Smith" }
    }
  ]
}
```

---

## Routes Configuration

**Location**: `routes/api.php`

### Public Routes (No Authentication Required)
```php
GET    /api/reviews/tradie/{tradieId}         - Get tradie reviews
GET    /api/reviews/tradie/{tradieId}/stats   - Get tradie statistics
GET    /api/reviews/job/{jobId}               - Get job review
```

### Protected Routes (Authentication Required)
```php
POST   /api/reviews                           - Submit a review
GET    /api/reviews/can-review/{jobId}        - Check if can review
GET    /api/reviews/my-reviews                - Get user's reviews
POST   /api/reviews/{reviewId}/helpful        - Mark as helpful
POST   /api/reviews/{reviewId}/report         - Report review
```

---

## How It Works - Flutter Frontend Integration

### 1. **User Submits Review**
- Homeowner completes a job and wants to leave a review
- Flutter app calls: `POST /api/reviews` with review data
- Data is validated and stored in `reviews` table
- Response includes confirmation and review details

### 2. **View Tradie Reviews**
- Flutter app displays tradie profile
- Calls: `GET /api/reviews/tradie/{tradieId}` to fetch reviews
- Calls: `GET /api/reviews/tradie/{tradieId}/stats` to fetch statistics
- Data is displayed with average rating, review count, and breakdown

### 3. **Report Inappropriate Review**
- User finds a review that is offensive/inappropriate
- Flutter app calls: `POST /api/reviews/{reviewId}/report`
- Report is stored in `review_reports` table with status 'pending'
- Admin can later review and take action

### 4. **Mark Review as Helpful**
- Other users find a review helpful
- Flutter app calls: `POST /api/reviews/{reviewId}/helpful`
- `helpful_count` is incremented for that review

---

## Database Status

✅ **Migration Files Created**:
- `2025_11_02_001425_create_reviews_table.php`
- `2025_11_02_001438_create_review_reports_table.php`
- `2025_11_02_012249_add_status_to_review_reports_table.php`

✅ **Models Created**:
- `app/Models/Review.php`
- `app/Models/ReviewReport.php`

✅ **Controller Created**:
- `app/Http/Controllers/Api/ReviewController.php`

✅ **Routes Configured**:
- `routes/api.php` - All review endpoints registered

---

## Running Migrations

To apply the migrations to your database:

```bash
php artisan migrate
```

To rollback migrations:

```bash
php artisan migrate:rollback
```

To refresh (drop and recreate all tables):

```bash
php artisan migrate:refresh
```

---

## Key Features

✅ **Complete Rating System**
- Overall 5-star rating
- Detailed category ratings (quality, performance, response time)
- Optional feedback text
- Image support

✅ **Review Management**
- Approval/moderation support (status field)
- Helpful counter for community engagement
- Hide inappropriate reviews

✅ **Reporting System**
- Users can report problematic reviews
- Multiple report reasons (spam, offensive, etc.)
- Admin notes field for resolution tracking

✅ **Security**
- One review per job per homeowner (unique constraint)
- Authentication required for submissions
- Authorization checks (only own jobs can be reviewed)
- Validation on all inputs

✅ **Analytics**
- Average ratings by tradie
- Rating distribution breakdown
- Detailed category averages
- Review statistics

---

## Testing the Endpoints

### Using cURL or Postman

**1. Submit Review**:
```bash
curl -X POST http://localhost/api/reviews \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "job_id": 123,
    "tradie_id": 456,
    "rating": 5,
    "feedback": "Excellent work!",
    "show_username": true
  }'
```

**2. Get Tradie Reviews**:
```bash
curl http://localhost/api/reviews/tradie/456
```

**3. Get Tradie Statistics**:
```bash
curl http://localhost/api/reviews/tradie/456/stats
```

---

## Summary

Your database is **fully configured** to store and manage:
- ✅ Reviews and ratings from homeowners
- ✅ Detailed service quality feedback
- ✅ Review reports/moderation
- ✅ User authentication and authorization
- ✅ Statistics and analytics

All submitted rates and reviews from your Flutter frontend will be securely stored in the `reviews` table with complete relationship tracking to jobs, homeowners, and tradies.
