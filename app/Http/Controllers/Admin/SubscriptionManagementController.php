<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'package_id' => $request->input('package_id'),
        ];

        $query = Subscription::with(['package', 'account.user']);

        if ($filters['search']) {
            $query->whereHas('account.user', function ($q) use ($filters) {
                $q->where('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['package_id']) {
            $query->where('package_id', $filters['package_id']);
        }

        $subscriptions = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        $packages = Package::where('is_active', true)->get();

        return Inertia::render('admin/subscriptions/index', [
            'subscriptions' => $subscriptions,
            'filters' => $filters,
            'packages' => $packages,
        ]);
    }
}
