<?php

namespace App\Http\Controllers;

//use App\Http\Controllers\Controller;
use App\Models\Tradie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\PersonalAccessToken; // used if auth returns a token instance

class TradieProfileController extends Controller
{
    /**
     * Update the authenticated tradie's profile.
     */
    public function update(Request $request)
    {
        // 1) Try the request user (preferred)
        $user = $request->user();

        $tradie = null;

        // If request->user() is a PersonalAccessToken (rare), get tokenable
        if ($user instanceof PersonalAccessToken) {
            $tradie = $user->tokenable;
        } elseif ($user instanceof Model) {
            $tradie = $user;
        }

        // 2) If still null, try a tradie guard explicitly
        if (! $tradie) {
            $tradie = Auth::guard('tradie')->user() ?? Auth::user();
        }

        // 3) If it's still not a model, attempt to resolve by id (fallback)
        if (! ($tradie instanceof Model)) {
            $possibleId = $user->id ?? $request->input('id') ?? null;
            if ($possibleId) {
                $tradie = Tradie::find($possibleId);
            }
        }

        // If we still don't have an Eloquent model, return 401/422
        if (! $tradie instanceof Model) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to resolve authenticated tradie. Please check your authentication guard/config.',
            ], 401);
        }

        // Validation
        $data = $request->validate([
            'first_name'         => 'required|string|max:100',
            'middle_name'        => 'nullable|string|max:100',
            'last_name'          => 'required|string|max:100',
            'phone'              => 'nullable|string|max:20',
            'bio'                => 'nullable|string',
            'address'            => 'nullable|string',
            'city'               => 'nullable|string|max:100',
            'region'             => 'nullable|string|max:100',
            'postal_code'        => 'nullable|string|max:20',
            'latitude'           => 'nullable|numeric',
            'longitude'          => 'nullable|numeric',
            'business_name'      => 'nullable|string|max:255',
            'license_number'     => 'nullable|string|max:255',
            'insurance_details'  => 'nullable|string',
            'years_experience'   => 'nullable|integer|min:0',
            'hourly_rate'        => 'nullable|numeric|min:0',
            'availability_status'=> ['nullable', Rule::in(['available', 'busy', 'unavailable'])],
            'service_radius'     => 'nullable|integer|min:0',
        ]);

        // 4) Protect against mass-assignment: only update fillable columns
        $fillable = (new Tradie())->getFillable();
        $safeData = array_intersect_key($data, array_flip($fillable));

        // 5) Update (use update() if you prefer)
        $tradie->fill($safeData);
        $tradie->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data'    => $tradie->fresh(),
        ]);
    }
}