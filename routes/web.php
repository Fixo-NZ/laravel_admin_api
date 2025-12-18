<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\HomeownerAuthController;

// Web route for viewing a homeowner profile
Route::get('/homeowners/{homeowner}', [HomeownerAuthController::class, 'show'])
    ->name('homeowners.show');

// Web route for viewing a tradie profile (accepts optional id or query param `record`)
use App\Http\Controllers\Web\TradieProfileController;
Route::get('/tradie-profile/{id?}', [TradieProfileController::class, 'show'])
    ->name('tradies.profile.show');

// NOTE: Filament registers its own admin page route for `tradie-profile/{record}`.
// Do NOT define a web route that redirects `/admin/tradie-profile/{record}` to `/admin`,
// otherwise clicking profile links will always send users back to the admin dashboard.

// Backwards-compatible redirect for legacy links that used `/admin/tradies/profile/{id}`
// Redirect legacy admin URLs to the public tradie profile path (no /admin prefix).
Route::get('/admin/tradies/profile/{id}', function ($id) {
    return redirect('/tradie-profile/' . $id);
});

// Provide a named route that Filament or other code can call via
// `route('filament.admin.pages.tradie-profile', ['record' => $id])`.
// This route uses a lightweight redirect to the canonical Filament admin
// page `/admin/tradie-profile/{id}` so URL generation succeeds without
// interfering with Filament's own route registration.
Route::get('/_filament_redirect/tradie-profile/{record?}', function ($record = null) {
    // If no record provided, send back to the site index.
    if (! $record) {
        return redirect('/');
    }

    return redirect('/tradie-profile/' . $record);
})->name('filament.admin.pages.tradie-profile');


// Booking history page (web)
Route::middleware('auth')->get('/bookings', function() {
    return view('bookings.index');
})->name('bookings.index');

    

