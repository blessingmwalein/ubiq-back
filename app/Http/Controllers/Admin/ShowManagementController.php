<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentItem;
use App\Models\Show;
use App\Models\Season;
use App\Models\Episode;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowManagementController extends Controller
{
    /**
     * Display a listing of shows.
     */
    public function index(Request $request): Response
    {
        $query = Show::with(['contentItem.category', 'contentItem.provider'])
            ->withCount(['seasons', 'episodes']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('contentItem', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $shows = $query->latest()->paginate(12);

        return Inertia::render('admin/shows/index', [
            'shows' => $shows,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new show.
     */
    public function create(): Response
    {
        // Get content items of type 'show' that don't have a show record yet
        $availableContent = ContentItem::where('type', 'show')
            ->whereDoesntHave('show')
            ->with(['category', 'provider'])
            ->get();

        return Inertia::render('admin/shows/form', [
            'availableContent' => $availableContent,
        ]);
    }

    /**
     * Store a newly created show in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content_item_id' => 'required|exists:content_items,id|unique:shows,content_item_id',
            'status' => 'required|in:ongoing,completed,cancelled',
        ]);

        $show = Show::create($validated);

        return redirect()->route('admin.shows.show', $show)
            ->with('success', 'Show created successfully.');
    }

    /**
     * Display the specified show with seasons and episodes.
     */
    public function show(Show $show): Response
    {
        $show->load([
            'contentItem.category',
            'contentItem.provider',
            'contentItem.videoAssets',
            'seasons.episodes.contentItem.videoAssets',
        ]);

        return Inertia::render('admin/shows/show', [
            'show' => $show,
        ]);
    }

    /**
     * Show the form for editing the specified show.
     */
    public function edit(Show $show): Response
    {
        $show->load('contentItem');

        return Inertia::render('admin/shows/form', [
            'show' => $show,
        ]);
    }

    /**
     * Update the specified show in storage.
     */
    public function update(Request $request, Show $show)
    {
        $validated = $request->validate([
            'status' => 'required|in:ongoing,completed,cancelled',
        ]);

        $show->update($validated);

        return redirect()->route('admin.shows.show', $show)
            ->with('success', 'Show updated successfully.');
    }

    /**
     * Remove the specified show from storage.
     */
    public function destroy(Show $show)
    {
        // Check if show has seasons
        if ($show->seasons()->count() > 0) {
            return back()->with('error', 'Cannot delete show with existing seasons. Delete seasons first.');
        }

        $show->delete();

        return redirect()->route('admin.shows.index')
            ->with('success', 'Show deleted successfully.');
    }

    /**
     * Store a new season for the show.
     */
    public function storeSeason(Request $request, Show $show)
    {
        $validated = $request->validate([
            'season_number' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($show) {
                    if ($show->seasons()->where('season_number', $value)->exists()) {
                        $fail('Season number already exists for this show.');
                    }
                },
            ],
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'poster_url' => 'nullable|string',
            'released_at' => 'nullable|date',
        ]);

        $season = $show->seasons()->create($validated);
        $show->updateTotals();

        return back()->with('success', 'Season created successfully.');
    }

    /**
     * Update the specified season.
     */
    public function updateSeason(Request $request, Show $show, Season $season)
    {
        if ($season->show_id !== $show->id) {
            abort(404);
        }

        $validated = $request->validate([
            'season_number' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($show, $season) {
                    if ($show->seasons()->where('season_number', $value)->where('id', '!=', $season->id)->exists()) {
                        $fail('Season number already exists for this show.');
                    }
                },
            ],
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'poster_url' => 'nullable|string',
            'released_at' => 'nullable|date',
        ]);

        $season->update($validated);

        return back()->with('success', 'Season updated successfully.');
    }

    /**
     * Remove the specified season.
     */
    public function destroySeason(Show $show, Season $season)
    {
        if ($season->show_id !== $show->id) {
            abort(404);
        }

        // Check if season has episodes
        if ($season->episodes()->count() > 0) {
            return back()->with('error', 'Cannot delete season with existing episodes. Delete episodes first.');
        }

        $season->delete();
        $show->updateTotals();

        return back()->with('success', 'Season deleted successfully.');
    }

    /**
     * Store a new episode for the season.
     */
    public function storeEpisode(Request $request, Show $show, Season $season)
    {
        if ($season->show_id !== $show->id) {
            abort(404);
        }

        $validated = $request->validate([
            'episode_number' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($season) {
                    if ($season->episodes()->where('episode_number', $value)->exists()) {
                        $fail('Episode number already exists for this season.');
                    }
                },
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_seconds' => 'nullable|integer|min:0',
            'thumbnail_url' => 'nullable|string',
            'released_at' => 'nullable|date',
        ]);

        // Don't create a separate content item for episodes
        // Episodes are part of the show's content item
        $validated['content_item_id'] = $show->content_item_id;

        $episode = $season->episodes()->create($validated);
        $season->updateEpisodeCount();
        $show->updateTotals();

        return back()->with('success', 'Episode created successfully.');
    }

    /**
     * Update the specified episode.
     */
    public function updateEpisode(Request $request, Show $show, Season $season, Episode $episode)
    {
        if ($season->show_id !== $show->id || $episode->season_id !== $season->id) {
            abort(404);
        }

        $validated = $request->validate([
            'episode_number' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($season, $episode) {
                    if ($season->episodes()->where('episode_number', $value)->where('id', '!=', $episode->id)->exists()) {
                        $fail('Episode number already exists for this season.');
                    }
                },
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_seconds' => 'nullable|integer|min:0',
            'thumbnail_url' => 'nullable|string',
            'released_at' => 'nullable|date',
        ]);

        // Update just the episode itself, no separate content item
        $episode->update($validated);

        return back()->with('success', 'Episode updated successfully.');
    }

    /**
     * Remove the specified episode.
     */
    public function destroyEpisode(Show $show, Season $season, Episode $episode)
    {
        if ($season->show_id !== $show->id || $episode->season_id !== $season->id) {
            abort(404);
        }

        $episode->delete();
        $season->updateEpisodeCount();
        $show->updateTotals();

        return back()->with('success', 'Episode deleted successfully.');
    }

    /**
     * Reorder episodes within a season.
     */
    public function reorderEpisodes(Request $request, Show $show, Season $season)
    {
        if ($season->show_id !== $show->id) {
            abort(404);
        }

        $validated = $request->validate([
            'episodes' => 'required|array',
            'episodes.*.id' => 'required|exists:episodes,id',
            'episodes.*.episode_number' => 'required|integer|min:1',
        ]);

        foreach ($validated['episodes'] as $episodeData) {
            Episode::where('id', $episodeData['id'])
                ->where('season_id', $season->id)
                ->update(['episode_number' => $episodeData['episode_number']]);
        }

        return back()->with('success', 'Episodes reordered successfully.');
    }
}
