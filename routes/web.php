<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\HomeownerAuthController;

// Web route for viewing a homeowner profile
Route::get('/homeowners/{homeowner}', [HomeownerAuthController::class, 'show'])
    ->name('homeowners.show');


    
