<?php

namespace App\Providers;

use App\Models\Business;
use App\Models\MessageThread;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\WatchlistItem;
use Illuminate\Support\Facades\Auth;
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
                'listings' => Business::where('status', 'active')->count(),
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
