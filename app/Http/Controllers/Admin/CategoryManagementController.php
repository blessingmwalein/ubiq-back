<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryManagementController extends Controller
{
    public function index(): Response
    {
        $categories = Category::withCount('contentItems')
            ->orderBy('sort_order', 'asc')
            ->orderBy('title', 'asc')
            ->get();

        return Inertia::render('admin/categories/index', [
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/categories/form', [
            'category' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:categories,key',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(string $id): Response
    {
        $category = Category::withCount('contentItems')->findOrFail($id);

        return Inertia::render('admin/categories/form', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:categories,key,' . $id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function toggleStatus(string $id): RedirectResponse
    {
        $category = Category::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();

        return back()->with('success', 'Category status updated successfully.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $category = Category::findOrFail($id);
        
        // Check if category has content
        $contentCount = $category->contentItems()->count();
        if ($contentCount > 0) {
            return back()->with('error', "Cannot delete category. It has {$contentCount} content items.");
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
