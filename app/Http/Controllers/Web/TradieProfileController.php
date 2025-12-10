<?php

namespace App\Http\Controllers\Web;


use App\Models\Tradie;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TradieProfileController extends Controller
{
    public function show($id)
    {
        $tradie = Tradie::with(['bookings.service'])->findOrFail($id);
        return view('filament.admin.pages.tradie-profile-page', compact('tradie'));
    }
}
