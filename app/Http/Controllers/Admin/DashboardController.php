<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\SubscriptionRepositoryInterface;
use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Contracts\Repositories\ContentRepositoryInterface;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private PaymentRepositoryInterface $paymentRepository,
        private ContentRepositoryInterface $contentRepository
    ) {}

    public function index(): Response
    {
        // Get analytics data - using basic model counts
        $totalUsers = \App\Models\User::count();
        $totalSubscriptions = \App\Models\Subscription::where('status', 'active')->count();
        $totalRevenue = \App\Models\Payment::where('status', 'completed')->sum('amount');
        $totalContent = \App\Models\ContentItem::where('visibility', 'public')->count();

        // Mock data for charts - replace with real queries
        $revenueByMonth = [
            ['month' => 'Jan', 'revenue' => 45000],
            ['month' => 'Feb', 'revenue' => 52000],
            ['month' => 'Mar', 'revenue' => 48000],
            ['month' => 'Apr', 'revenue' => 61000],
            ['month' => 'May', 'revenue' => 55000],
            ['month' => 'Jun', 'revenue' => 67000],
        ];

        $topContent = [
            ['title' => 'Stranger Things', 'views' => 1250000],
            ['title' => 'The Crown', 'views' => 980000],
            ['title' => 'Bridgerton', 'views' => 875000],
            ['title' => 'Money Heist', 'views' => 720000],
            ['title' => 'The Witcher', 'views' => 650000],
        ];

        $subscriptionsByPackage = [
            ['package' => 'Basic', 'count' => 1250],
            ['package' => 'Standard', 'count' => 2100],
            ['package' => 'Premium', 'count' => 890],
        ];

        return Inertia::render('admin/dashboard', [
            'analytics' => [
                'totalUsers' => $totalUsers,
                'totalSubscriptions' => $totalSubscriptions,
                'totalRevenue' => $totalRevenue,
                'totalContent' => $totalContent,
                'activeUsers' => $totalUsers, // Replace with actual active users count
                'newUsers' => 150, // Replace with actual new users count
                'revenueGrowth' => 15.3, // Replace with actual calculation
                'contentViews' => 5245000, // Replace with actual views count
                'revenueByMonth' => $revenueByMonth,
                'topContent' => $topContent,
                'subscriptionsByPackage' => $subscriptionsByPackage,
            ],
        ]);
    }
}
