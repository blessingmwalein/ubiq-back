<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentProvider;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContentProviderManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ContentProvider::query()->with('owner');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $query->where(function($q) use ($request) {
                $q->where('display_name', 'like', '%' . $request->search . '%')
                  ->orWhere('contact_email', 'like', '%' . $request->search . '%');
            });
        }

        $providers = $query->withCount('contentItems')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('admin/providers/index', [
            'providers' => $providers,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
            ],
        ]);
    }

    public function create(): Response
    {
        $users = \App\Models\User::where('role', 'provider')->get();

        return Inertia::render('admin/providers/form', [
            'provider' => null,
            'users' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'owner_id' => 'required|exists:users,id',
            'display_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|url|max:500',
            'status' => 'required|in:pending,approved,suspended,rejected',
            'revenue_share_percentage' => 'required|numeric|min:0|max:100',
        ]);

        ContentProvider::create($validated);

        return redirect()->route('admin.providers.index')
            ->with('success', 'Content provider created successfully.');
    }

    public function edit(string $id): Response
    {
        $provider = ContentProvider::with(['owner', 'contentItems'])->findOrFail($id);
        $users = \App\Models\User::where('role', 'provider')->get();

        return Inertia::render('admin/providers/form', [
            'provider' => $provider,
            'users' => $users,
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $provider = ContentProvider::findOrFail($id);

        $validated = $request->validate([
            'owner_id' => 'required|exists:users,id',
            'display_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|url|max:500',
            'status' => 'required|in:pending,approved,suspended,rejected',
            'revenue_share_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $provider->update($validated);

        return redirect()->route('admin.providers.index')
            ->with('success', 'Content provider updated successfully.');
    }

    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        $provider = ContentProvider::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,suspended,rejected',
        ]);

        $provider->update($validated);

        $statusText = ucfirst($validated['status']);
        return back()->with('success', "Provider status changed to {$statusText}.");
    }

    public function destroy(string $id): RedirectResponse
    {
        $provider = ContentProvider::findOrFail($id);
        
        // Check if provider has content
        $contentCount = $provider->contentItems()->count();
        if ($contentCount > 0) {
            return back()->with('error', "Cannot delete provider. They have {$contentCount} content items.");
        }

        $provider->delete();

        return redirect()->route('admin.providers.index')
            ->with('success', 'Content provider deleted successfully.');
    }
}
