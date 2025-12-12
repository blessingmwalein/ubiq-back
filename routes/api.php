<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\ContinueWatchingController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PlaybackController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ShowController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\WatchHistoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes (Public)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('api.auth.register');
    Route::post('login', [AuthController::class, 'login'])->name('api.auth.login');
    Route::post('login/social', [AuthController::class, 'socialLogin'])->name('api.auth.social-login');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('api.auth.forgot-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('api.auth.reset-password');
    
    // Protected auth routes
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('me', [AuthController::class, 'me'])->name('api.auth.me');
    });
});

// Packages Routes (Public)
Route::prefix('packages')->group(function () {
    Route::get('/', [PackageController::class, 'index'])->name('api.packages.index');
    Route::get('{id}', [PackageController::class, 'show'])->name('api.packages.show');
});

// Interests Routes (Public)
Route::get('interests', [OnboardingController::class, 'getInterests'])->name('api.interests.index');

// Content Routes (Public/Mixed)
Route::prefix('content')->group(function () {
    Route::get('/', [ContentController::class, 'index'])->name('api.content.index');
    Route::get('search', [ContentController::class, 'search'])->name('api.content.search');
    Route::get('trending', [ContentController::class, 'trending'])->name('api.content.trending');
    Route::get('featured', [ContentController::class, 'featured'])->name('api.content.featured');
    Route::get('most-viewed', [ContentController::class, 'mostViewed'])->name('api.content.most-viewed');
    Route::get('recently-added', [ContentController::class, 'recentlyAdded'])->name('api.content.recently-added');
    Route::get('new-releases', [ContentController::class, 'newReleases'])->name('api.content.new-releases');
    Route::get('category/{categoryId}', [ContentController::class, 'byCategory'])->name('api.content.by-category');
    Route::get('genre/{slug}', [ContentController::class, 'byGenre'])->name('api.content.by-genre');
    Route::get('type/{type}', [ContentController::class, 'byType'])->name('api.content.by-type');
    Route::get('{id}/similar', [ContentController::class, 'similar'])->name('api.content.similar');
    Route::get('{id}', [ContentController::class, 'show'])->name('api.content.show');
});

// Protected content routes (require authentication)
Route::middleware('auth:api')->group(function () {
    Route::get('content/recommendations', [ContentController::class, 'recommendations'])->name('api.content.recommendations');
});

// Shows & Episodes Routes (Public)
Route::prefix('shows')->name('api.shows.')->group(function () {
    Route::get('/', [ShowController::class, 'index'])->name('index');
    Route::get('/search', [ShowController::class, 'search'])->name('search');
    Route::get('/{uuid}', [ShowController::class, 'show'])->name('show');
    Route::get('/{uuid}/seasons', [ShowController::class, 'seasons'])->name('seasons');
    Route::get('/{showUuid}/seasons/{seasonNumber}', [ShowController::class, 'season'])->name('season');
});

Route::prefix('episodes')->name('api.episodes.')->group(function () {
    Route::get('/{uuid}', [ShowController::class, 'episode'])->name('show');
    Route::get('/{uuid}/next', [ShowController::class, 'nextEpisode'])->name('next');
});

// Categories Routes (Public)
Route::get('categories', [ContentController::class, 'categories'])->name('api.categories.index');

// Protected Routes (Require Authentication)
Route::middleware('auth:api')->group(function () {
    
    // Onboarding Routes
    Route::prefix('onboarding')->name('api.onboarding.')->group(function () {
        Route::post('complete', [OnboardingController::class, 'completeOnboarding'])->name('complete');
        Route::post('interests', [OnboardingController::class, 'updateInterests'])->name('interests');
        Route::get('progress', [OnboardingController::class, 'getProgress'])->name('progress');
    });
    
    // Device Management Routes
    Route::prefix('devices')->name('api.devices.')->group(function () {
        Route::get('/', [DeviceController::class, 'index'])->name('index');
        Route::post('/register', [DeviceController::class, 'register'])->name('register');
        Route::post('/verify', [DeviceController::class, 'verify'])->name('verify');
        Route::post('/logout', [DeviceController::class, 'logout'])->name('logout');
        Route::post('/logout-all', [DeviceController::class, 'logoutAll'])->name('logout-all');
        Route::get('/statistics', [DeviceController::class, 'statistics'])->name('statistics');
        Route::delete('/{deviceUuid}', [DeviceController::class, 'destroy'])->name('destroy');
    });
    
    // Profile Management Routes
    Route::prefix('profiles')->name('api.profiles.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::post('/', [ProfileController::class, 'store'])->name('store');
        Route::get('/{uuid}', [ProfileController::class, 'show'])->name('show');
        Route::put('/{uuid}', [ProfileController::class, 'update'])->name('update');
        Route::delete('/{uuid}', [ProfileController::class, 'destroy'])->name('destroy');
        Route::post('/{uuid}/switch', [ProfileController::class, 'switch'])->name('switch');
        Route::get('/{uuid}/statistics', [ProfileController::class, 'statistics'])->name('statistics');
    });
    
    // Account & Profile Routes (Legacy - consider deprecating)
    Route::prefix('accounts')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('api.accounts.index');
        Route::get('{id}', [AccountController::class, 'show'])->name('api.accounts.show');
        Route::post('{accountId}/profiles', [AccountController::class, 'createProfile'])->name('api.accounts.profiles.create');
    });
    
    Route::prefix('profiles')->group(function () {
        Route::put('{profileId}', [AccountController::class, 'updateProfile'])->name('api.profiles.legacy.update');
        Route::delete('{profileId}', [AccountController::class, 'deleteProfile'])->name('api.profiles.legacy.delete');
    });
    
    // Subscription Routes
    Route::prefix('subscriptions')->name('api.subscriptions.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::post('/', [SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('/renew', [SubscriptionController::class, 'renew'])->name('renew');
    });
    
    // Payment Routes
    Route::prefix('payments')->group(function () {
        Route::get('history', [PaymentController::class, 'history'])->name('api.payments.history');
        Route::get('{id}', [PaymentController::class, 'show'])->name('api.payments.show');
    });
    
    // Playback Routes
    Route::prefix('playback')->name('api.playback.')->group(function () {
        Route::post('token', [PlaybackController::class, 'requestToken'])->name('token');
        Route::get('stream/{token}', [PlaybackController::class, 'getStreamUrl'])->name('stream');
        Route::get('validate/{token}', [PlaybackController::class, 'validateToken'])->name('validate');
        Route::get('hls/{token}', [PlaybackController::class, 'getHlsManifest'])->name('hls');
        Route::get('qualities/{contentId}', [PlaybackController::class, 'getQualityOptions'])->name('qualities');
        Route::get('tracks/{contentId}', [PlaybackController::class, 'getTracks'])->name('tracks');
        Route::post('progress', [PlaybackController::class, 'updateProgress'])->name('progress');
        Route::get('resume/{contentId}/{profileId}', [PlaybackController::class, 'getResumePosition'])->name('resume');
        Route::post('error', [PlaybackController::class, 'reportError'])->name('error');
    });
    
    // Continue Watching Routes
    Route::prefix('continue-watching')->name('api.continue-watching.')->group(function () {
        Route::get('/', [ContinueWatchingController::class, 'index'])->name('index');
        Route::post('/complete', [ContinueWatchingController::class, 'markCompleted'])->name('complete');
        Route::delete('/', [ContinueWatchingController::class, 'remove'])->name('remove');
    });
    
    Route::get('recently-watched', [ContinueWatchingController::class, 'recentlyWatched'])->name('api.recently-watched');
    
    // Watch History Routes
    Route::prefix('watch-history')->name('api.watch-history.')->group(function () {
        Route::get('continue', [WatchHistoryController::class, 'continueWatching'])->name('continue');
        Route::get('recent', [WatchHistoryController::class, 'recentlyWatched'])->name('recent');
        Route::post('progress', [WatchHistoryController::class, 'updateProgress'])->name('progress');
    });
    
    // Favorites Routes
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index'])->name('api.favorites.index');
        Route::post('{contentId}/toggle', [FavoriteController::class, 'toggle'])->name('api.favorites.toggle');
    });
});

// Admin Routes (Require Admin Role)
Route::middleware(['auth:api', 'role:admin'])->prefix('admin')->group(function () {
    
    // Admin Content Management
    Route::prefix('content')->group(function () {
        Route::post('/', [AdminContentController::class, 'store'])->name('api.admin.content.store');
        Route::put('{id}', [AdminContentController::class, 'update'])->name('api.admin.content.update');
        Route::delete('{id}', [AdminContentController::class, 'destroy'])->name('api.admin.content.destroy');
        Route::post('{id}/publish', [AdminContentController::class, 'publish'])->name('api.admin.content.publish');
        Route::post('{id}/unpublish', [AdminContentController::class, 'unpublish'])->name('api.admin.content.unpublish');
    });
});
