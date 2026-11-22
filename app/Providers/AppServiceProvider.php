<?php

namespace App\Providers;

use App\Http\Controllers\RealDashboardController;
use App\Models\MessageThread;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\WatchlistItem;
use App\Services\BuyBoxMatchingService;
use App\Services\Listings\ListingFeed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Admin area (listings, imports, users). Use with ->middleware('can:admin').
        Gate::define('admin', fn (User $user): bool => $user->isAdmin());

        View::composer('*', function ($view): void {
            $data = $view->getData();
            if (! isset($data['userName'])) {
                $user = Auth::user();
                $view->with('userName', $user?->name ?? 'Guest');
                $view->with('userEmail', $user?->email ?? '');
            }
            $view->with('isDemoPreview', ! Auth::check());
        });

        // Real counts for the dashboard chrome (sidebar badges, top bar, notifications).
        View::composer(['dashboard.partials.sidebar', 'dashboard.partials.topbar'], function ($view): void {
            $user = Auth::user() ?? User::where('email', config('nextkeepers.demo_email'))->first();
            $view->with('nav', [
                // Guests: sample rows in the preview. Accounts: real listings matching their buy box.
                'listings' => ! $user ? 0 : (app(ListingFeed::class)->showsRealListings($user)
                    ? app(RealDashboardController::class)->matchCount($user, app(BuyBoxMatchingService::class)->getOrCreateBuyBox($user))
                    : app(ListingFeed::class)->for($user)->count()),
                'watchlist' => $user ? WatchlistItem::where('user_id', $user->id)->count() : 0,
                'unreadMessages' => $user ? MessageThread::where('user_id', $user->id)->where('unread', true)->count() : 0,
                'unreadNotifications' => $user ? UserNotification::where('user_id', $user->id)->where('is_read', false)->count() : 0,
                'notifications' => $user ? UserNotification::where('user_id', $user->id)->latest('id')->limit(5)->get() : collect(),
                'isDemo' => ! Auth::check(),
                'initial' => strtoupper(mb_substr(Auth::user()?->name ?: 'G', 0, 1)),
            ]);
        });
    }
}
