<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateContentRequest;
use App\Services\ContentService;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\ContentRepositoryInterface;
use App\Models\ContentProvider;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ContentManagementController extends Controller
{
    public function __construct(
        private ContentService $contentService,
        private ContentRepositoryInterface $contentRepository,
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->input('search'),
            'type' => $request->input('type'),
            'status' => $request->input('status'),
            'category_id' => $request->input('category_id'),
        ];

        // Build query using Eloquent
        $query = \App\Models\ContentItem::query();
        
        if ($filters['search']) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }
        
        if ($filters['type']) {
            $query->where('type', $filters['type']);
        }
        
        if ($filters['status']) {
            $query->where('visibility', $filters['status']);
        }
        
        if ($filters['category_id']) {
            $query->where('category_id', $filters['category_id']);
        }

        $content = $query->with(['category', 'provider', 'show'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $categories = $this->categoryRepository->all();

        return Inertia::render('admin/content/index', [
            'content' => $content,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        $categories = $this->categoryRepository->all();
        $providers = ContentProvider::where('status', 'approved')->get();

        return Inertia::render('admin/content/form', [
            'categories' => $categories,
            'providers' => $providers,
        ]);
    }

    public function store(CreateContentRequest $request): RedirectResponse
    {
        $dto = \App\DTOs\CreateContentDTO::fromRequest($request->validated());
        $content = $this->contentService->createContent($dto);

        return redirect()->route('admin.content.index')
            ->with('success', 'Content created successfully.');
    }

    public function edit(string $id): Response
    {
        $content = \App\Models\ContentItem::with(['category', 'provider'])->findOrFail($id);
        $categories = $this->categoryRepository->all();
        $providers = ContentProvider::where('status', 'approved')->get();

        return Inertia::render('admin/content/form', [
            'content' => $content,
            'categories' => $categories,
            'providers' => $providers,
        ]);
    }

    public function show(string $id): Response
    {
        $content = \App\Models\ContentItem::with([
            'category',
            'provider',
            'show.seasons.episodes.contentItem.videoAssets',
            'videoAssets'
        ])->findOrFail($id);

        return Inertia::render('admin/content/view', [
            'content' => $content,
        ]);
    }

    public function update(CreateContentRequest $request, string $id): RedirectResponse
    {
        $dto = \App\DTOs\UpdateContentDTO::fromRequest($request->validated());
        $content = $this->contentService->updateContent($id, $dto);

        return redirect()->route('admin.content.index')
            ->with('success', 'Content updated successfully.');
    }

    /**
     * Update only image URLs for content
     */
    public function updateImages(Request $request, string $id)
    {
        $request->validate([
            'poster_url' => 'nullable|string',
            'backdrop_url' => 'nullable|string',
            'thumbnail_url' => 'nullable|string',
        ]);

        $content = \App\Models\ContentItem::findOrFail($id);
        
        // Only update fields that are provided
        $updateData = array_filter([
            'poster_url' => $request->input('poster_url'),
            'backdrop_url' => $request->input('backdrop_url'),
            'thumbnail_url' => $request->input('thumbnail_url'),
        ], fn($value) => !is_null($value));

        $content->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Images updated successfully',
            'content' => $content,
        ]);
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->contentService->deleteContent($id);

        return redirect()->route('admin.content.index')
            ->with('success', 'Content deleted successfully.');
    }

    public function publish(string $id): RedirectResponse
    {
        $this->contentService->publishContent($id);

        return back()->with('success', 'Content published successfully.');
    }

    public function unpublish(string $id): RedirectResponse
    {
        $this->contentService->unpublishContent($id);

        return back()->with('success', 'Content unpublished successfully.');
    }
}
