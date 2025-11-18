<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Show;
use App\Models\Season;
use App\Models\Episode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    /**
     * Get all shows with pagination.
     * 
     * GET /api/shows?per_page=20&page=1
     * GET /api/shows?per_page=20&all=1  (for debugging - shows all regardless of published status)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 20);
        
        $query = Show::with(['contentItem.category']);
        
        // Only filter by published status if not requesting all
        if (!$request->boolean('all')) {
            $query->whereHas('contentItem', function ($q) {
                $q->where('visibility', 'public')
                  ->whereNotNull('published_at')
                  ->where('published_at', '<=', now());
            });
        }
        
        $shows = $query->latest('created_at')->paginate($perPage);

        return response()->json($shows);
    }

    /**
     * Get show details with seasons.
     * 
     * GET /api/shows/{uuid}
     */
    public function show(string $uuid): JsonResponse
    {
        $show = Show::with([
            'contentItem.category',
            'seasons' => function ($query) {
                $query->orderBy('season_number');
            },
            'seasons.episodes' => function ($query) {
                $query->orderBy('episode_number')
                    ->select('id', 'uuid', 'season_id', 'episode_number', 'title', 'description', 'duration_seconds', 'thumbnail_url');
            }
        ])
        ->where('uuid', $uuid)
        ->firstOrFail();

        return response()->json([
            'data' => $show,
        ]);
    }

    /**
     * Get show seasons.
     * 
     * GET /api/shows/{uuid}/seasons
     */
    public function seasons(string $uuid): JsonResponse
    {
        $show = Show::where('uuid', $uuid)->firstOrFail();
        
        $seasons = $show->seasons()
            ->with(['episodes' => function ($query) {
                $query->orderBy('episode_number')
                    ->select('id', 'uuid', 'season_id', 'episode_number', 'title', 'description', 'duration_seconds', 'thumbnail_url');
            }])
            ->orderBy('season_number')
            ->get();

        return response()->json([
            'data' => $seasons,
        ]);
    }

    /**
     * Get specific season with episodes.
     * 
     * GET /api/shows/{showUuid}/seasons/{seasonNumber}
     */
    public function season(string $showUuid, int $seasonNumber): JsonResponse
    {
        $show = Show::where('uuid', $showUuid)->firstOrFail();
        
        $season = Season::with(['episodes' => function ($query) {
                $query->orderBy('episode_number');
            }])
            ->where('show_id', $show->id)
            ->where('season_number', $seasonNumber)
            ->firstOrFail();

        return response()->json([
            'data' => $season,
        ]);
    }

    /**
     * Get specific episode.
     * 
     * GET /api/episodes/{uuid}
     */
    public function episode(string $uuid): JsonResponse
    {
        $episode = Episode::with([
            'season.show.contentItem',
            'videoAssets' => function ($query) {
                $query->where('status', 'completed')
                    ->orderBy('quality', 'desc');
            }
        ])
        ->where('uuid', $uuid)
        ->firstOrFail();

        return response()->json([
            'data' => $episode,
        ]);
    }

    /**
     * Get next episode.
     * 
     * GET /api/episodes/{uuid}/next
     */
    public function nextEpisode(string $uuid): JsonResponse
    {
        $currentEpisode = Episode::with('season')->where('uuid', $uuid)->firstOrFail();
        
        // Try to get next episode in same season
        $nextEpisode = Episode::where('season_id', $currentEpisode->season_id)
            ->where('episode_number', '>', $currentEpisode->episode_number)
            ->orderBy('episode_number')
            ->first();

        // If no next episode in season, get first episode of next season
        if (!$nextEpisode) {
            $nextSeason = Season::where('show_id', $currentEpisode->season->show_id)
                ->where('season_number', '>', $currentEpisode->season->season_number)
                ->orderBy('season_number')
                ->first();

            if ($nextSeason) {
                $nextEpisode = Episode::where('season_id', $nextSeason->id)
                    ->orderBy('episode_number')
                    ->first();
            }
        }

        if (!$nextEpisode) {
            return response()->json([
                'data' => null,
                'message' => 'No next episode available',
            ]);
        }

        return response()->json([
            'data' => $nextEpisode->load('season'),
        ]);
    }

    /**
     * Search shows.
     * 
     * GET /api/shows/search?q=breaking+bad
     * GET /api/shows/search?q=query&all=1  (for debugging - shows all)
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = $request->get('q');
        $perPage = $request->get('per_page', 20);
        $showAll = $request->boolean('all');

        $shows = Show::with('contentItem.category')
            ->whereHas('contentItem', function ($q) use ($query, $showAll) {
                if (!$showAll) {
                    $q->where('visibility', 'public')
                      ->whereNotNull('published_at')
                      ->where('published_at', '<=', now());
                }
                $q->where(function ($subQ) use ($query) {
                    $subQ->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                });
            })
            ->paginate($perPage);

        return response()->json($shows);
    }
}
