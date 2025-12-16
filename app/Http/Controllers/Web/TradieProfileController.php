<?php

namespace App\Http\Controllers\Web;


use App\Models\Tradie;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TradieProfileController extends Controller
{
    public function show()
    {
        $id = request('record');

        if (! $id) {
            abort(404);
        }

        $tradie = \App\Models\Tradie::with(['bookings.service'])->findOrFail($id);

        return view('filament.admin.pages.tradie-profile-page', compact('tradie'));
    }
}
