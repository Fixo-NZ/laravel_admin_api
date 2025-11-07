<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Review;
use App\Models\ReviewReport;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReviewStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalReviews = Review::count();
        $approvedReviews = Review::where('status', 'approved')->count();
        $pendingReviews = Review::where('status', 'pending')->count();
        $averageRating = Review::where('status', 'approved')->avg('rating');
        $pendingReports = ReviewReport::where('status', 'pending')->count();
        
        // Calculate trend (last 7 days vs previous 7 days)
        $lastWeekReviews = Review::where('created_at', '>=', now()->subDays(7))->count();
        $previousWeekReviews = Review::whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count();
        $reviewTrend = $previousWeekReviews > 0 
            ? round((($lastWeekReviews - $previousWeekReviews) / $previousWeekReviews) * 100, 1)
            : 0;

        return [
            Stat::make('Total Reviews', $totalReviews)
                ->description($reviewTrend >= 0 ? "+{$reviewTrend}% from last week" : "{$reviewTrend}% from last week")
                ->descriptionIcon($reviewTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($reviewTrend >= 0 ? 'success' : 'danger')
                ->chart([7, 4, 6, 8, 10, 12, $lastWeekReviews]),

            Stat::make('Average Rating', number_format($averageRating, 1) . ' ⭐')
                ->description($approvedReviews . ' approved reviews')
                ->descriptionIcon('heroicon-m-star')
                ->color('success'),

            Stat::make('Pending Reviews', $pendingReviews)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(route('filament.admin.resources.reviews.index', ['tableFilters' => ['status' => ['value' => 'pending']]]))
                ->extraAttributes(['class' => 'cursor-pointer']),

            Stat::make('Pending Reports', $pendingReports)
                ->description('Need attention')
                ->descriptionIcon('heroicon-m-flag')
                ->color($pendingReports > 0 ? 'danger' : 'success')
                ->url(route('filament.admin.resources.review-reports.index', ['tableFilters' => ['status' => ['value' => 'pending']]]))
                ->extraAttributes(['class' => 'cursor-pointer']),
        ];
    }
}