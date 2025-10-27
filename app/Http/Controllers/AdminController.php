<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    /**
     * Display a list of all admin users
     *
     * Notes & Explanations:
     * - roles unnecessary since admins, tradies, and homeowners are in separate tables 
     *   unless we have different classes of admins
     * - Using `User::where('role', 'admin')->get()` retrieves all users whose role is 'admin'.
     * - `compact('admins')` passes the $admins variable to the Blade view.
     * - For large datasets, consider pagination instead of `get()` to avoid performance issues:
     *      User::where('role', 'admin')->paginate(20);
     * - You can also add sorting or filtering functionality directly in this method if needed.
     */
    public function indexAdmins()
    {
        // Step 1: Fetch all users with the 'admin' role
        // roles unneccessary since admins, tradies, and homeowners are in separate tables unless we have different classes of admins
        $admins = User::where('role', 'admin')->get(); // fetch only admin users

        // Step 2: Return the admin overview view and pass the retrieved admins
        return view('admin.admin_overview', compact('admins'));
    }
}
