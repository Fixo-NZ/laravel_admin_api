<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\HomeownerAuthController;

// Web route for viewing a homeowner profile
Route::get('/homeowners/{homeowner}', [HomeownerAuthController::class, 'show'])
    ->name('homeowners.show');

// Web route for viewing a tradie profile
use App\Http\Controllers\Web\TradieProfileController;
Route::get('/tradies/profile/{id}', [TradieProfileController::class, 'show'])
    ->name('tradies.profile.show');


// Booking history page (web)
Route::middleware('auth')->get('/bookings', function() {
    return view('bookings.index');
})->name('bookings.index');

    

