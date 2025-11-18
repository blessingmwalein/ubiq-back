<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class PackageManagementController extends Controller
{
    public function index(): Response
    {
        $packages = Package::orderBy('price_monthly', 'asc')->get();

        return Inertia::render('admin/packages/index', [
            'packages' => $packages,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/packages/form', [
            'package' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:packages,key',
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'max_profiles' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['trial_days'] = $validated['trial_days'] ?? 0;

        Package::create($validated);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package created successfully.');
    }

    public function edit(string $id): Response
    {
        $package = Package::findOrFail($id);

        return Inertia::render('admin/packages/form', [
            'package' => $package,
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $package = Package::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:packages,key,' . $id,
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'max_profiles' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['trial_days'] = $validated['trial_days'] ?? 0;

        $package->update($validated);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package updated successfully.');
    }

    public function toggleStatus(string $id): RedirectResponse
    {
        $package = Package::findOrFail($id);
        $package->is_active = !$package->is_active;
        $package->save();

        return back()->with('success', 'Package status updated successfully.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $package = Package::findOrFail($id);
        
        // Check if package has active subscriptions
        $hasActiveSubscriptions = $package->subscriptions()
            ->where('status', 'active')
            ->exists();

        if ($hasActiveSubscriptions) {
            return back()->with('error', 'Cannot delete package with active subscriptions.');
        }

        $package->delete();

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package deleted successfully.');
    }
}
