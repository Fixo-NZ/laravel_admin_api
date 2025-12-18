<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\HomeownerAuthController;

// Web route for viewing a homeowner profile
Route::get('/homeowners/{homeowner}', [HomeownerAuthController::class, 'show'])
    ->name('homeowners.show');

// Web route for viewing a tradie profile
use App\Http\Controllers\Web\TradieProfileController;
Route::get('/tradie-profile', [TradieProfileController::class, 'show'])
    ->name('tradies.profile.show');

// Temporary named route to satisfy Filament layout links for the tradie profile page.
// Filament expects a route named `filament.admin.pages.tradie-profile` when rendering
// its layout; define a redirect to the admin dashboard to avoid RouteNotFoundException
Route::get('/admin/tradie-profile/{record?}', function ($record = null) {
    return redirect('/admin');
})->name('filament.admin.pages.tradie-profile');


// Booking history page (web)
Route::middleware('auth')->get('/bookings', function () {
    return view('bookings.index');
})->name('bookings.index');



