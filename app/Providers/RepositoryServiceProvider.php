<?php

namespace App\Providers;

use App\Contracts\Repositories\AccountRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\ContentRepositoryInterface;
use App\Contracts\Repositories\FavoriteRepositoryInterface;
use App\Contracts\Repositories\PackageRepositoryInterface;
use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Contracts\Repositories\PlaybackRepositoryInterface;
use App\Contracts\Repositories\ProfileRepositoryInterface;
use App\Contracts\Repositories\SubscriptionRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\VideoAssetRepositoryInterface;
use App\Contracts\Repositories\WatchHistoryRepositoryInterface;
use App\Repositories\Eloquent\EloquentAccountRepository;
use App\Repositories\Eloquent\EloquentCategoryRepository;
use App\Repositories\Eloquent\EloquentContentRepository;
use App\Repositories\Eloquent\EloquentFavoriteRepository;
use App\Repositories\Eloquent\EloquentPackageRepository;
use App\Repositories\Eloquent\EloquentPaymentRepository;
use App\Repositories\Eloquent\EloquentPlaybackRepository;
use App\Repositories\Eloquent\EloquentProfileRepository;
use App\Repositories\Eloquent\EloquentSubscriptionRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Repositories\Eloquent\EloquentVideoAssetRepository;
use App\Repositories\Eloquent\EloquentWatchHistoryRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interfaces to Eloquent implementations
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(AccountRepositoryInterface::class, EloquentAccountRepository::class);
        $this->app->bind(ProfileRepositoryInterface::class, EloquentProfileRepository::class);
        $this->app->bind(ContentRepositoryInterface::class, EloquentContentRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(PackageRepositoryInterface::class, EloquentPackageRepository::class);
        $this->app->bind(SubscriptionRepositoryInterface::class, EloquentSubscriptionRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, EloquentPaymentRepository::class);
        $this->app->bind(PlaybackRepositoryInterface::class, EloquentPlaybackRepository::class);
        $this->app->bind(VideoAssetRepositoryInterface::class, EloquentVideoAssetRepository::class);
        $this->app->bind(WatchHistoryRepositoryInterface::class, EloquentWatchHistoryRepository::class);
        $this->app->bind(FavoriteRepositoryInterface::class, EloquentFavoriteRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function bootstrap(): void
    {
        //
    }
}
