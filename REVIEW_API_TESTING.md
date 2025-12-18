# Review System - API Testing Guide

This guide contains curl commands for testing all review endpoints from the Flutter frontend perspective.

## Prerequisites

- Your API is running at `http://localhost` (adjust the URL as needed)
- You have valid authentication tokens for Homeowner and Tradie users
- Sample data: homeowner_id=1, tradie_id=1, job_id=1

Replace these with your actual IDs and tokens.

---

## 1. Authentication - Get Tokens

### Homeowner Login
```bash
curl -X POST http://localhost/api/homeowner/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "homeowner@example.com",
    "password": "password"
  }'
```

Response will include `token` - use this for authenticated requests.

### Tradie Login
```bash
curl -X POST http://localhost/api/tradie/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "tradie@example.com",
    "password": "password"
  }'
```

---

## 2. Review Submission (Homeowner Only)

### Submit a Review
```bash
curl -X POST http://localhost/api/reviews \
  -H "Authorization: Bearer YOUR_HOMEOWNER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "job_id": 1,
    "tradie_id": 1,
    "rating": 5,
    "feedback": "Excellent workmanship and very professional!",
    "service_quality_rating": 5,
    "service_quality_comment": "High quality materials used",
    "performance_rating": 4,
    "performance_comment": "Finished on time",
    "contractor_service_rating": 5,
    "response_time_rating": 5,
    "best_feature": "Attention to detail",
    "show_username": true
  }'
```

**Expected Response (201 Created)**:
```json
{
  "success": true,
  "message": "Review submitted successfully",
  "data": {
    "id": 1,
    "job_id": 1,
    "homeowner_id": 123,
    "tradie_id": 1,
    "rating": 5,
    "feedback": "Excellent workmanship and very professional!",
    "status": "approved",
    "created_at": "2025-11-24T10:30:00Z",
    "homeowner": {
      "id": 123,
      "first_name": "John",
      "last_name": "Doe"
    },
    "tradie": {
      "id": 1,
      "first_name": "Jane",
      "last_name": "Smith"
    }
  }
}
```

### Submit a Review with Minimal Data
```bash
curl -X POST http://localhost/api/reviews \
  -H "Authorization: Bearer YOUR_HOMEOWNER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "job_id": 1,
    "tradie_id": 1,
    "rating": 4,
    "feedback": "Good work overall"
  }'
```

---

## 3. Check if Job Can Be Reviewed

```bash
curl -X GET http://localhost/api/reviews/can-review/1 \
  -H "Authorization: Bearer YOUR_HOMEOWNER_TOKEN"
```

**Response if can review (200 OK)**:
```json
{
  "success": true,
  "can_review": true,
  "job": {
    "id": 1,
    "title": "Kitchen Renovation",
    "status": "completed"
  }
}
```

**Response if already reviewed (200 OK)**:
```json
{
  "success": false,
  "can_review": false,
  "message": "You have already reviewed this job"
}
```

---

## 4. View Tradie Reviews (Public)

### Get All Reviews for a Tradie
```bash
curl -X GET "http://localhost/api/reviews/tradie/1?page=1"
```

**Response (200 OK)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "job_id": 1,
      "homeowner_id": 123,
      "tradie_id": 1,
      "rating": 5,
      "feedback": "Excellent work!",
      "service_quality_rating": 5,
      "performance_rating": 4,
      "status": "approved",
      "show_username": true,
      "helpful_count": 2,
      "created_at": "2025-11-24T10:30:00Z",
      "homeowner": {
        "id": 123,
        "first_name": "John",
        "last_name": "D"
      }
    }
  ],
  "stats": {
    "average_rating": 4.8,
    "total_reviews": 5,
    "rating_breakdown": {
      "5": 4,
      "4": 1,
      "3": 0,
      "2": 0,
      "1": 0
    }
  }
}
```

---

## 5. Get Tradie Statistics (Public)

```bash
curl -X GET http://localhost/api/reviews/tradie/1/stats
```

**Response (200 OK)**:
```json
{
  "success": true,
  "data": {
    "total_reviews": 5,
    "average_rating": 4.8,
    "rating_breakdown": {
      "5": {
        "count": 4,
        "percentage": 80.0
      },
      "4": {
        "count": 1,
        "percentage": 20.0
      },
      "3": {
        "count": 0,
        "percentage": 0.0
      },
      "2": {
        "count": 0,
        "percentage": 0.0
      },
      "1": {
        "count": 0,
        "percentage": 0.0
      }
    },
    "detailed_ratings": {
      "service_quality": 4.8,
      "performance": 4.6,
      "contractor_service": 4.8,
      "response_time": 4.8
    }
  }
}
```

---

## 6. Get Job Review (Public)

```bash
curl -X GET http://localhost/api/reviews/job/1
```

**Response (200 OK)**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "job_id": 1,
    "homeowner_id": 123,
    "tradie_id": 1,
    "rating": 5,
    "feedback": "Excellent work!",
    "status": "approved",
    "created_at": "2025-11-24T10:30:00Z",
    "homeowner": {
      "id": 123,
      "first_name": "John",
      "last_name": "Doe"
    },
    "tradie": {
      "id": 1,
      "first_name": "Jane",
      "last_name": "Smith"
    }
  }
}
```

---

## 7. Get My Reviews (Authenticated Homeowner)

```bash
curl -X GET "http://localhost/api/reviews/my-reviews?page=1" \
  -H "Authorization: Bearer YOUR_HOMEOWNER_TOKEN"
```

**Response (200 OK)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "job_id": 1,
      "homeowner_id": 123,
      "tradie_id": 1,
      "rating": 5,
      "feedback": "Excellent work!",
      "status": "approved",
      "created_at": "2025-11-24T10:30:00Z",
      "tradie": {
        "id": 1,
        "first_name": "Jane",
        "last_name": "Smith"
      }
    }
  ]
}
```

---

## 8. Mark Review as Helpful

```bash
curl -X POST http://localhost/api/reviews/1/helpful \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response (200 OK)**:
```json
{
  "success": true,
  "message": "Review marked as helpful",
  "helpful_count": 3
}
```

---

## 9. Report a Review

```bash
curl -X POST http://localhost/api/reviews/1/report \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reason": "offensive",
    "description": "This review contains inappropriate language and offensive comments"
  }'
```

**Valid Reasons**:
- `spam` - Spam or irrelevant content
- `offensive` - Offensive or rude language
- `inappropriate` - Inappropriate content
- `fake` - Fake or misleading review
- `other` - Other reasons

**Response (201 Created)**:
```json
{
  "success": true,
  "message": "Review reported successfully. Our team will review it shortly.",
  "data": {
    "id": 5,
    "review_id": 1,
    "reporter_type": "App\\Models\\Homeowner",
    "reporter_id": 123,
    "reason": "offensive",
    "status": "pending",
    "created_at": "2025-11-24T10:35:00Z"
  }
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "errors": {
    "job_id": ["The job_id field is required."],
    "rating": ["The rating must be between 1 and 5."]
  }
}
```

### Unauthorized (401)
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### Forbidden (403)
```json
{
  "success": false,
  "message": "You have already reviewed this job"
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Job not found"
}
```

---

## Integration Checklist for Flutter Frontend

- [ ] User can submit a review after job completion
- [ ] Review submission includes optional fields (service quality, performance, etc.)
- [ ] User can view all reviews for a tradie
- [ ] User can see tradie statistics (average rating, breakdown)
- [ ] User can view review for a specific job
- [ ] User can check if they can review a job before submission
- [ ] User can mark a review as helpful
- [ ] User can report inappropriate reviews
- [ ] User can view their own submitted reviews
- [ ] Error messages are displayed appropriately

---

## Database Queries for Verification

You can verify data is being stored correctly by running these SQL queries:

### Check all reviews
```sql
SELECT * FROM reviews;
```

### Check reviews for specific tradie
```sql
SELECT * FROM reviews WHERE tradie_id = 1;
```

### Check reviews submitted by specific homeowner
```sql
SELECT * FROM reviews WHERE homeowner_id = 123;
```

### Check review reports
```sql
SELECT * FROM review_reports WHERE status = 'pending';
```

### Check tradie stats
```sql
SELECT 
  tradie_id,
  COUNT(*) as total_reviews,
  ROUND(AVG(rating), 2) as average_rating,
  MIN(rating) as min_rating,
  MAX(rating) as max_rating
FROM reviews
WHERE status = 'approved'
GROUP BY tradie_id;
```

---

## Postman Collection

You can import these endpoints into Postman for easier testing. Create a new collection and add the endpoints with the curl commands above, replacing placeholder values with your actual test data.

---

## Troubleshooting

### "Job not found or not eligible for review"
- Verify the job exists in the database
- Verify the job is marked as 'completed'
- Verify the authenticated user is the homeowner of the job

### "You have already reviewed this job"
- The homeowner has already submitted a review for this job (one per job)
- To update, the review would need to be edited (feature not yet implemented)

### 401 Unauthenticated
- Token is missing or expired
- Token format should be: `Authorization: Bearer {token}`

### Database Connection Issues
- Verify `.env` file has correct database credentials
- Run `php artisan migrate` to apply migrations
- Check Laravel logs in `storage/logs/`
