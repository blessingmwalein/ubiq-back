<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\FavoriteRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct(
        private FavoriteRepositoryInterface $favoriteRepository
    ) {
    }

    /**
     * Get favorites for profile.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
        ]);

        $favorites = $this->favoriteRepository->getByProfile($request->profile_id);

        return response()->json([
            'data' => $favorites,
        ]);
    }

    /**
     * Toggle favorite.
     */
    public function toggle(Request $request, int $contentId): JsonResponse
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
        ]);

        $isFavorited = $this->favoriteRepository->toggleFavorite(
            $request->profile_id,
            $contentId
        );

        return response()->json([
            'message' => $isFavorited ? 'Added to favorites' : 'Removed from favorites',
            'data' => [
                'is_favorited' => $isFavorited,
            ],
        ]);
    }
}
