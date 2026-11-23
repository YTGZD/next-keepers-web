<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealBoxController;
use App\Http\Controllers\EarlyAccessController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');

Route::post('/early-access', [EarlyAccessController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('early-access.store');

// Authentication (the forms live in the site-wide modal)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:6,1'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->middleware(['guest', 'throttle:6,1'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Dashboard, public read-only preview. Guests see the shared sample account;
// signed-in users see their own. Every page here is read-only.
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/discover', [DashboardController::class, 'discover'])->name('discover');
Route::get('/businesses', [DashboardController::class, 'discover'])->name('businesses');
Route::get('/business/{id?}', [DashboardController::class, 'showBusiness'])->name('business.show');
Route::get('/buy-box', [DashboardController::class, 'buyBox'])->name('buybox');
Route::get('/market-intelligence', [DashboardController::class, 'marketIntelligence'])->name('market.intelligence');
Route::get('/watchlist', [DashboardController::class, 'watchlist'])->name('watchlist');
Route::get('/messages', [DashboardController::class, 'messages'])->name('messages');
Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');

// Everything that writes data requires a real account.
Route::middleware('auth')->group(function () {
    Route::post('/business/{businessId}/inquire', [DashboardController::class, 'submitInquiry'])->name('business.inquire');
    Route::post('/buy-box', [DashboardController::class, 'updateBuyBox'])->name('buybox.update');
    Route::get('/buy-box/results', [DealBoxController::class, 'results'])->middleware('throttle:120,1')->name('dealbox.results');
    Route::post('/deal-boxes', [DealBoxController::class, 'store'])->middleware('throttle:30,1')->name('dealbox.store');
    Route::delete('/deal-boxes/{dealBox}', [DealBoxController::class, 'destroy'])->whereNumber('dealBox')->name('dealbox.destroy');
    Route::post('/watchlist/toggle/{businessId}', [DashboardController::class, 'toggleWatchlist'])->name('watchlist.toggle');
    Route::delete('/watchlist/{businessId}', [DashboardController::class, 'removeWatchlistItem'])->name('watchlist.remove');
    Route::post('/pipeline/toggle/{businessId}', [DashboardController::class, 'togglePipeline'])->name('pipeline.toggle');
    Route::post('/messages/{threadId}/send', [DashboardController::class, 'sendMessage'])->name('messages.send');
    Route::post('/messages/{threadId}/read', [DashboardController::class, 'markThreadRead'])->name('messages.read');
    Route::post('/settings/profile', [DashboardController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/password', [DashboardController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/criteria', [DashboardController::class, 'updateCriteria'])->name('settings.criteria');
    Route::post('/settings/notifications', [DashboardController::class, 'updateNotifications'])->name('settings.notifications');
    Route::post('/notifications/read-all', [DashboardController::class, 'markNotificationsRead'])->name('notifications.read-all');
});

// Newsletter signup is open to everyone (throttled).
Route::post('/newsletter/subscribe', [DashboardController::class, 'subscribeNewsletter'])
    ->middleware('throttle:5,1')
    ->name('newsletter.subscribe');
