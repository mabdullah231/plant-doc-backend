<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    // Get details of a single user with associated company
    public function getUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user);
    }

    // Get all users with associated companies
    public function getAllUsers()
    {
        $currentUserId = Auth::id();

        // Retrieve all users except the current authenticated user
        $users = User::where('id', '!=', $currentUserId)
        ->where('user_type', '!=', '0')
        ->get();


        // Return the users as a JSON response
        return response()->json($users);
    }

    // Activate or deactivate a user
    public function toggleUserStatus($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json(['message' => 'User status updated', 'is_active' => $user->is_active]);
    }
    public function makeSubadmin($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User  not found'], 404);
        }


        $user->user_type = "1";
        $user->save();

        return response()->json(['message' => 'User  has been made a subadmin', 'is_subadmin' => $user->is_subadmin]);
    }
}
