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
    public function getUserDetails()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'User profile retrieved successfully.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user profile.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateUserDetails(Request $request)
    {
        $user = Auth::user(); 

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|min:8|confirmed', 
        ]);

        if ($request->has('password') && $request->has('password_confirmation')) {
            if ($request->password !== $request->password_confirmation) {
                return response()->json(['message' => 'Passwords do not match.'], 400);
            }

            $user->password = bcrypt($request->password);
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        $user->save();

        return response()->json([
            'message' => 'User details updated successfully.',
            'user' => $user
        ]);
    }
}
