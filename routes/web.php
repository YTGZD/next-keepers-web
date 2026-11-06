<?php

use App\Http\Controllers\EarlyAccessController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');

Route::post('/early-access', [EarlyAccessController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('early-access.store');
