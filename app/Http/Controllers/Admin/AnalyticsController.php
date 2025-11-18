<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function index(): Response
    {
        // Get real analytics data
        $totalUsers = \App\Models\User::count();
        $totalSubscriptions = \App\Models\Subscription::where('status', 'active')->count();
        $totalRevenue = \App\Models\Payment::where('status', 'completed')->sum('amount');
        $totalContent = \App\Models\ContentItem::where('visibility', 'public')->count();
        $activeUsers = \App\Models\User::where('updated_at', '>=', now()->subDays(30))->count();
        $contentViews = \App\Models\ContentItem::sum('views_count');

        // Mock data for charts - replace with real aggregated queries
        $revenueByMonth = [
            ['month' => 'Jan', 'revenue' => 45000],
            ['month' => 'Feb', 'revenue' => 52000],
            ['month' => 'Mar', 'revenue' => 48000],
            ['month' => 'Apr', 'revenue' => 61000],
            ['month' => 'May', 'revenue' => 55000],
            ['month' => 'Jun', 'revenue' => 67000],
        ];

        $userGrowth = [
            ['month' => 'Jan', 'users' => 1200],
            ['month' => 'Feb', 'users' => 1450],
            ['month' => 'Mar', 'users' => 1680],
            ['month' => 'Apr', 'users' => 1920],
            ['month' => 'May', 'users' => 2100],
            ['month' => 'Jun', 'users' => 2350],
        ];

        $topContent = \App\Models\ContentItem::where('visibility', 'public')
            ->orderBy('views_count', 'desc')
            ->take(5)
            ->get(['title', 'views_count as views'])
            ->toArray();

        $topCategories = [
            ['category' => 'Action', 'views' => 1250000],
            ['category' => 'Drama', 'views' => 980000],
            ['category' => 'Comedy', 'views' => 875000],
            ['category' => 'Thriller', 'views' => 720000],
        ];

        $deviceBreakdown = [
            ['device' => 'Mobile', 'percentage' => 45],
            ['device' => 'Desktop', 'percentage' => 35],
            ['device' => 'Tablet', 'percentage' => 15],
            ['device' => 'Smart TV', 'percentage' => 5],
        ];

        $subscriptionsByPackage = \App\Models\Package::withCount([
            'subscriptions' => function ($query) {
                $query->where('status', 'active');
            }
        ])->get()->map(function ($package) {
            return [
                'package' => $package->name,
                'count' => $package->subscriptions_count,
            ];
        })->toArray();

        return Inertia::render('admin/analytics/index', [
            'analytics' => [
                'totalUsers' => $totalUsers,
                'totalSubscriptions' => $totalSubscriptions,
                'totalRevenue' => $totalRevenue,
                'totalContent' => $totalContent,
                'activeUsers' => $activeUsers,
                'newUsers' => 150, // Replace with actual calculation
                'revenueGrowth' => 15.3, // Replace with actual calculation
                'contentViews' => $contentViews,
                'revenueByMonth' => $revenueByMonth,
                'userGrowth' => $userGrowth,
                'topContent' => $topContent,
                'topCategories' => $topCategories,
                'deviceBreakdown' => $deviceBreakdown,
                'subscriptionsByPackage' => $subscriptionsByPackage,
            ],
        ]);
    }
}
