<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ContentManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\PackageManagementController;
use App\Http\Controllers\Admin\SubscriptionManagementController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\CategoryManagementController;
use App\Http\Controllers\Admin\ContentProviderManagementController;
use App\Http\Controllers\Admin\ShowManagementController;
use App\Http\Controllers\Admin\FileUploadController;

// Admin Routes - Protected by auth and role:admin middleware
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Content Management
    Route::prefix('content')->name('content.')->group(function () {
        Route::get('/', [ContentManagementController::class, 'index'])->name('index');
        Route::get('/create', [ContentManagementController::class, 'create'])->name('create');
        Route::post('/', [ContentManagementController::class, 'store'])->name('store');
        Route::get('/{id}', [ContentManagementController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ContentManagementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ContentManagementController::class, 'update'])->name('update');
        Route::patch('/{id}/images', [ContentManagementController::class, 'updateImages'])->name('update-images');
        Route::delete('/{id}', [ContentManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/publish', [ContentManagementController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [ContentManagementController::class, 'unpublish'])->name('unpublish');
    });
    
    // Categories Management
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryManagementController::class, 'index'])->name('index');
        Route::get('/create', [CategoryManagementController::class, 'create'])->name('create');
        Route::post('/', [CategoryManagementController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [CategoryManagementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CategoryManagementController::class, 'update'])->name('update');
        Route::post('/{id}/toggle-status', [CategoryManagementController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{id}', [CategoryManagementController::class, 'destroy'])->name('destroy');
    });
    
    // Content Providers Management
    Route::prefix('providers')->name('providers.')->group(function () {
        Route::get('/', [ContentProviderManagementController::class, 'index'])->name('index');
        Route::get('/create', [ContentProviderManagementController::class, 'create'])->name('create');
        Route::post('/', [ContentProviderManagementController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [ContentProviderManagementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ContentProviderManagementController::class, 'update'])->name('update');
        Route::post('/{id}/status', [ContentProviderManagementController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{id}', [ContentProviderManagementController::class, 'destroy'])->name('destroy');
    });
    
    // Shows Management
    Route::prefix('shows')->name('shows.')->group(function () {
        Route::get('/', [ShowManagementController::class, 'index'])->name('index');
        Route::get('/create', [ShowManagementController::class, 'create'])->name('create');
        Route::post('/', [ShowManagementController::class, 'store'])->name('store');
        Route::get('/{show}', [ShowManagementController::class, 'show'])->name('show');
        Route::get('/{show}/edit', [ShowManagementController::class, 'edit'])->name('edit');
        Route::put('/{show}', [ShowManagementController::class, 'update'])->name('update');
        Route::delete('/{show}', [ShowManagementController::class, 'destroy'])->name('destroy');
        
        // Seasons Management
        Route::post('/{show}/seasons', [ShowManagementController::class, 'storeSeason'])->name('seasons.store');
        Route::put('/{show}/seasons/{season}', [ShowManagementController::class, 'updateSeason'])->name('seasons.update');
        Route::delete('/{show}/seasons/{season}', [ShowManagementController::class, 'destroySeason'])->name('seasons.destroy');
        
        // Episodes Management
        Route::post('/{show}/seasons/{season}/episodes', [ShowManagementController::class, 'storeEpisode'])->name('seasons.episodes.store');
        Route::put('/{show}/seasons/{season}/episodes/{episode}', [ShowManagementController::class, 'updateEpisode'])->name('seasons.episodes.update');
        Route::delete('/{show}/seasons/{season}/episodes/{episode}', [ShowManagementController::class, 'destroyEpisode'])->name('seasons.episodes.destroy');
        Route::post('/{show}/seasons/{season}/episodes/reorder', [ShowManagementController::class, 'reorderEpisodes'])->name('seasons.episodes.reorder');
    });
    
    // Users Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserManagementController::class, 'destroy'])->name('destroy');
    });
    
    // Packages Management
    Route::prefix('packages')->name('packages.')->group(function () {
        Route::get('/', [PackageManagementController::class, 'index'])->name('index');
        Route::get('/create', [PackageManagementController::class, 'create'])->name('create');
        Route::post('/', [PackageManagementController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [PackageManagementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PackageManagementController::class, 'update'])->name('update');
        Route::post('/{id}/toggle-status', [PackageManagementController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{id}', [PackageManagementController::class, 'destroy'])->name('destroy');
    });
    
    // Subscriptions Management
    Route::get('subscriptions', [SubscriptionManagementController::class, 'index'])->name('subscriptions.index');
    
    // Analytics
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics');
    
    // File Uploads
    Route::prefix('upload')->name('upload.')->group(function () {
        Route::post('/image', [FileUploadController::class, 'uploadImage'])->name('image');
        Route::post('/video', [FileUploadController::class, 'uploadVideo'])->name('video');
        Route::post('/chunk', [FileUploadController::class, 'uploadChunk'])->name('chunk');
        Route::delete('/file', [FileUploadController::class, 'deleteFile'])->name('delete');
    });
    
    // Video Assets Management
    Route::delete('video-assets/{id}', [FileUploadController::class, 'deleteVideoAsset'])->name('video-assets.destroy');
    
    // Settings (placeholder for future implementation)
    // Route::get('settings', [SettingsController::class, 'index'])->name('settings');
});
