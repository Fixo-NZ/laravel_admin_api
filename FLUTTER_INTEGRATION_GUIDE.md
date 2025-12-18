# Flutter Frontend Integration - Review System

This guide explains how to integrate the review/rating system into your Flutter frontend application.

## Base Configuration

```dart
// lib/config/api_config.dart
class ApiConfig {
  static const String baseUrl = 'https://your-api-domain/api';
  static const String reviewsEndpoint = '$baseUrl/reviews';
}
```

---

## Review Models

Create these Dart models to match the backend:

```dart
// lib/models/review.dart
class Review {
  final int id;
  final int jobId;
  final int homeownerId;
  final int tradieId;
  final int rating;
  final String? feedback;
  final int? serviceQualityRating;
  final String? serviceQualityComment;
  final int? performanceRating;
  final String? performanceComment;
  final int? contractorServiceRating;
  final int? responseTimeRating;
  final String? bestFeature;
  final List<String>? images;
  final bool showUsername;
  final int helpfulCount;
  final String status;
  final DateTime createdAt;
  final DateTime updatedAt;

  Review({
    required this.id,
    required this.jobId,
    required this.homeownerId,
    required this.tradieId,
    required this.rating,
    this.feedback,
    this.serviceQualityRating,
    this.serviceQualityComment,
    this.performanceRating,
    this.performanceComment,
    this.contractorServiceRating,
    this.responseTimeRating,
    this.bestFeature,
    this.images,
    required this.showUsername,
    required this.helpfulCount,
    required this.status,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Review.fromJson(Map<String, dynamic> json) {
    return Review(
      id: json['id'],
      jobId: json['job_id'],
      homeownerId: json['homeowner_id'],
      tradieId: json['tradie_id'],
      rating: json['rating'],
      feedback: json['feedback'],
      serviceQualityRating: json['service_quality_rating'],
      serviceQualityComment: json['service_quality_comment'],
      performanceRating: json['performance_rating'],
      performanceComment: json['performance_comment'],
      contractorServiceRating: json['contractor_service_rating'],
      responseTimeRating: json['response_time_rating'],
      bestFeature: json['best_feature'],
      images: List<String>.from(json['images'] ?? []),
      showUsername: json['show_username'] ?? true,
      helpfulCount: json['helpful_count'] ?? 0,
      status: json['status'],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }
}
```

```dart
// lib/models/review_report.dart
class ReviewReport {
  final int id;
  final int reviewId;
  final String reporterType;
  final int reporterId;
  final String reason;
  final String? description;
  final String status;
  final String? adminNotes;
  final DateTime createdAt;
  final DateTime updatedAt;

  ReviewReport({
    required this.id,
    required this.reviewId,
    required this.reporterType,
    required this.reporterId,
    required this.reason,
    this.description,
    required this.status,
    this.adminNotes,
    required this.createdAt,
    required this.updatedAt,
  });

  factory ReviewReport.fromJson(Map<String, dynamic> json) {
    return ReviewReport(
      id: json['id'],
      reviewId: json['review_id'],
      reporterType: json['reporter_type'],
      reporterId: json['reporter_id'],
      reason: json['reason'],
      description: json['description'],
      status: json['status'],
      adminNotes: json['admin_notes'],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }
}
```

---

## Review Service

Create a service to handle all review API calls:

```dart
// lib/services/review_service.dart
import 'package:dio/dio.dart';
import 'package:your_app/models/review.dart';
import 'package:your_app/config/api_config.dart';

class ReviewService {
  final Dio _dio;
  
  ReviewService(this._dio);

  /// Submit a new review
  Future<Review> submitReview({
    required int jobId,
    required int tradieId,
    required int rating,
    String? feedback,
    int? serviceQualityRating,
    String? serviceQualityComment,
    int? performanceRating,
    String? performanceComment,
    int? contractorServiceRating,
    int? responseTimeRating,
    String? bestFeature,
    bool showUsername = true,
  }) async {
    try {
      final response = await _dio.post(
        '${ApiConfig.reviewsEndpoint}',
        data: {
          'job_id': jobId,
          'tradie_id': tradieId,
          'rating': rating,
          'feedback': feedback,
          'service_quality_rating': serviceQualityRating,
          'service_quality_comment': serviceQualityComment,
          'performance_rating': performanceRating,
          'performance_comment': performanceComment,
          'contractor_service_rating': contractorServiceRating,
          'response_time_rating': responseTimeRating,
          'best_feature': bestFeature,
          'show_username': showUsername,
        },
      );

      if (response.statusCode == 201 && response.data['success']) {
        return Review.fromJson(response.data['data']);
      } else {
        throw Exception(response.data['message'] ?? 'Failed to submit review');
      }
    } catch (e) {
      throw Exception('Error submitting review: $e');
    }
  }

  /// Check if a job can be reviewed
  Future<bool> canReviewJob(int jobId) async {
    try {
      final response = await _dio.get(
        '${ApiConfig.reviewsEndpoint}/can-review/$jobId',
      );

      if (response.statusCode == 200) {
        return response.data['can_review'] ?? false;
      }
      return false;
    } catch (e) {
      throw Exception('Error checking review eligibility: $e');
    }
  }

  /// Get all reviews for a tradie
  Future<Map<String, dynamic>> getTradieReviews(int tradieId, {int page = 1}) async {
    try {
      final response = await _dio.get(
        '${ApiConfig.reviewsEndpoint}/tradie/$tradieId',
        queryParameters: {'page': page},
      );

      if (response.statusCode == 200 && response.data['success']) {
        return {
          'reviews': (response.data['data'] as List)
              .map((r) => Review.fromJson(r))
              .toList(),
          'stats': response.data['stats'],
        };
      } else {
        throw Exception('Failed to fetch tradie reviews');
      }
    } catch (e) {
      throw Exception('Error fetching tradie reviews: $e');
    }
  }

  /// Get tradie statistics
  Future<Map<String, dynamic>> getTradieStats(int tradieId) async {
    try {
      final response = await _dio.get(
        '${ApiConfig.reviewsEndpoint}/tradie/$tradieId/stats',
      );

      if (response.statusCode == 200 && response.data['success']) {
        return response.data['data'];
      } else {
        throw Exception('Failed to fetch tradie statistics');
      }
    } catch (e) {
      throw Exception('Error fetching tradie statistics: $e');
    }
  }

  /// Get review for a specific job
  Future<Review> getJobReview(int jobId) async {
    try {
      final response = await _dio.get(
        '${ApiConfig.reviewsEndpoint}/job/$jobId',
      );

      if (response.statusCode == 200 && response.data['success']) {
        return Review.fromJson(response.data['data']);
      } else {
        throw Exception('Review not found');
      }
    } catch (e) {
      throw Exception('Error fetching job review: $e');
    }
  }

  /// Get user's reviews
  Future<List<Review>> getMyReviews({int page = 1}) async {
    try {
      final response = await _dio.get(
        '${ApiConfig.reviewsEndpoint}/my-reviews',
        queryParameters: {'page': page},
      );

      if (response.statusCode == 200 && response.data['success']) {
        return (response.data['data'] as List)
            .map((r) => Review.fromJson(r))
            .toList();
      } else {
        throw Exception('Failed to fetch your reviews');
      }
    } catch (e) {
      throw Exception('Error fetching your reviews: $e');
    }
  }

  /// Mark review as helpful
  Future<int> markReviewAsHelpful(int reviewId) async {
    try {
      final response = await _dio.post(
        '${ApiConfig.reviewsEndpoint}/$reviewId/helpful',
      );

      if (response.statusCode == 200) {
        return response.data['helpful_count'] ?? 0;
      } else {
        throw Exception('Failed to mark review as helpful');
      }
    } catch (e) {
      throw Exception('Error marking review as helpful: $e');
    }
  }

  /// Report a review
  Future<void> reportReview({
    required int reviewId,
    required String reason,
    String? description,
  }) async {
    try {
      final response = await _dio.post(
        '${ApiConfig.reviewsEndpoint}/$reviewId/report',
        data: {
          'reason': reason,
          'description': description,
        },
      );

      if (response.statusCode != 201 || !response.data['success']) {
        throw Exception(response.data['message'] ?? 'Failed to report review');
      }
    } catch (e) {
      throw Exception('Error reporting review: $e');
    }
  }
}
```

---

## Review Screen Widget Example

```dart
// lib/screens/review/submit_review_screen.dart
import 'package:flutter/material.dart';
import 'package:your_app/models/review.dart';
import 'package:your_app/services/review_service.dart';

class SubmitReviewScreen extends StatefulWidget {
  final int jobId;
  final int tradieId;
  final ReviewService reviewService;

  const SubmitReviewScreen({
    required this.jobId,
    required this.tradieId,
    required this.reviewService,
    Key? key,
  }) : super(key: key);

  @override
  State<SubmitReviewScreen> createState() => _SubmitReviewScreenState();
}

class _SubmitReviewScreenState extends State<SubmitReviewScreen> {
  final _formKey = GlobalKey<FormState>();
  int _rating = 5;
  String _feedback = '';
  int _serviceQualityRating = 5;
  String _serviceQualityComment = '';
  int _performanceRating = 5;
  String _performanceComment = '';
  bool _showUsername = true;
  bool _isSubmitting = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Submit Review'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Overall Rating
              const Text(
                'Overall Rating',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 8),
              Center(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: List.generate(5, (index) {
                    return GestureDetector(
                      onTap: () {
                        setState(() {
                          _rating = index + 1;
                        });
                      },
                      child: Icon(
                        Icons.star,
                        size: 40,
                        color: index < _rating ? Colors.amber : Colors.grey,
                      ),
                    );
                  }),
                ),
              ),
              const SizedBox(height: 24),

              // Feedback Text
              TextFormField(
                maxLines: 5,
                decoration: const InputDecoration(
                  labelText: 'Your Review (Optional)',
                  hintText: 'Share your experience...',
                  border: OutlineInputBorder(),
                ),
                onChanged: (value) => _feedback = value,
              ),
              const SizedBox(height: 16),

              // Service Quality Rating
              const Text(
                'Service Quality',
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
              Row(
                children: List.generate(5, (index) {
                  return Expanded(
                    child: GestureDetector(
                      onTap: () {
                        setState(() {
                          _serviceQualityRating = index + 1;
                        });
                      },
                      child: Icon(
                        Icons.star,
                        color: index < _serviceQualityRating
                            ? Colors.amber
                            : Colors.grey,
                      ),
                    ),
                  );
                }),
              ),
              const SizedBox(height: 16),

              // Show Username Toggle
              CheckboxListTile(
                title: const Text('Show my username on this review'),
                value: _showUsername,
                onChanged: (value) {
                  setState(() {
                    _showUsername = value ?? true;
                  });
                },
              ),
              const SizedBox(height: 24),

              // Submit Button
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _isSubmitting ? null : _submitReview,
                  child: _isSubmitting
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Text('Submit Review'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _submitReview() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);

    try {
      final review = await widget.reviewService.submitReview(
        jobId: widget.jobId,
        tradieId: widget.tradieId,
        rating: _rating,
        feedback: _feedback.isNotEmpty ? _feedback : null,
        serviceQualityRating: _serviceQualityRating,
        showUsername: _showUsername,
      );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Review submitted successfully!')),
        );
        Navigator.pop(context, review);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: ${e.toString()}')),
        );
      }
    } finally {
      setState(() => _isSubmitting = false);
    }
  }
}
```

---

## View Tradie Reviews Screen

```dart
// lib/screens/review/tradie_reviews_screen.dart
class TradieReviewsScreen extends StatefulWidget {
  final int tradieId;
  final ReviewService reviewService;

  const TradieReviewsScreen({
    required this.tradieId,
    required this.reviewService,
    Key? key,
  }) : super(key: key);

  @override
  State<TradieReviewsScreen> createState() => _TradieReviewsScreenState();
}

class _TradieReviewsScreenState extends State<TradieReviewsScreen> {
  late Future<Map<String, dynamic>> _reviewsFuture;

  @override
  void initState() {
    super.initState();
    _reviewsFuture = widget.reviewService.getTradieReviews(widget.tradieId);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Tradie Reviews')),
      body: FutureBuilder<Map<String, dynamic>>(
        future: _reviewsFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }

          if (snapshot.hasError) {
            return Center(
              child: Text('Error: ${snapshot.error}'),
            );
          }

          final reviews = snapshot.data?['reviews'] as List<Review>? ?? [];
          final stats = snapshot.data?['stats'] as Map<String, dynamic>? ?? {};

          return ListView(
            children: [
              // Statistics Card
              Card(
                margin: const EdgeInsets.all(16),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Average Rating: ${stats['average_rating'] ?? 0}',
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      Text(
                        'Total Reviews: ${stats['total_reviews'] ?? 0}',
                      ),
                    ],
                  ),
                ),
              ),

              // Reviews List
              ...reviews.map((review) => ReviewCard(review: review)),
            ],
          );
        },
      ),
    );
  }
}
```

---

## Review Card Widget

```dart
// lib/widgets/review_card.dart
class ReviewCard extends StatelessWidget {
  final Review review;

  const ReviewCard({required this.review, Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Rating Stars
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: List.generate(5, (index) {
                    return Icon(
                      Icons.star,
                      color: index < review.rating ? Colors.amber : Colors.grey,
                      size: 16,
                    );
                  }),
                ),
                Text('${review.rating}/5'),
              ],
            ),
            const SizedBox(height: 8),

            // Username
            if (review.showUsername)
              Text(
                'Anonymous',
                style: Theme.of(context).textTheme.bodySmall,
              ),
            const SizedBox(height: 8),

            // Feedback
            if (review.feedback != null && review.feedback!.isNotEmpty)
              Text(review.feedback!),
            const SizedBox(height: 8),

            // Date
            Text(
              review.createdAt.toString().split(' ')[0],
              style: Theme.of(context).textTheme.bodySmall,
            ),
          ],
        ),
      ),
    );
  }
}
```

---

## Integration Checklist

- [ ] Create Review model
- [ ] Create ReviewReport model
- [ ] Create ReviewService with all API methods
- [ ] Create SubmitReviewScreen
- [ ] Create ViewReviewsScreen
- [ ] Add ReviewCard widget
- [ ] Wire up navigation to review screens
- [ ] Test all review operations
- [ ] Handle error states
- [ ] Add loading states
- [ ] Test with Postman first (see REVIEW_API_TESTING.md)

---

## Best Practices

1. **Always check canReviewJob** before showing the review form
2. **Use try-catch** in service methods to handle errors gracefully
3. **Show loading states** while API calls are in progress
4. **Validate form data** before submission
5. **Provide user feedback** with SnackBars for success/error messages
6. **Implement pagination** when fetching reviews
7. **Cache reviews** to reduce API calls
8. **Handle offline mode** gracefully

---

## Troubleshooting

**No reviews displayed:**
- Check if tradie has any reviews in the database
- Verify API endpoint is correct
- Check if reviews are marked as 'approved' status

**Cannot submit review:**
- Verify job exists and is marked as 'completed'
- Verify user is authenticated with valid token
- Check if already reviewed (one review per job)

**401 Unauthorized:**
- Token may be expired, require re-authentication
- Check token is being sent in Authorization header

---

For API testing before integrating in Flutter, see `REVIEW_API_TESTING.md`
