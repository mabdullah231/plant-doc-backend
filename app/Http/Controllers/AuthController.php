<?php

namespace App\Http\Controllers;

use App\Jobs\VerifyEmailJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' =>"2",
        ]);

        $this->sendCode($user->id);

        return response()->json(['message' => 'Registration successful. Please verify your email.', 'user_id' => $user->id], 200);
    }

    public function sendCode($user_id)
    {
        $user = User::find($user_id);
        $code = rand(100000, 999999);
        VerifyEmailJob::dispatch(['email' => $user->email, 'code' => $code]);
        $user->update(['code' => $code]);
    }

    public function resendEmail(Request $request)
    {
        $request->validate(['user_id' => 'required|numeric']);
        $this->sendCode($request->user_id);
        return response()->json(['message' => 'Verification code resent.'], 200);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate(['code' => 'required|digits:6|numeric', 'user_id' => 'required|numeric']);
        $user = User::find($request->user_id);
        if ($user && $user->code == $request->code) {
            $user->update(['email_verified_at' => Carbon::now(), 'code' => null]);
            $token = $user->createToken('user_token')->plainTextToken;
            return response()->json(['message' => 'Email verified.', 'user' => $user, 'token' => $token], 200);
        }
        return response()->json(['message' => 'Invalid verification code.'], 422);
    }

    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        if (Auth::attempt($request->only('email', 'password'))) {
            // $user = Auth::user();
            $user = User::find(Auth::id());

            if (!$user->hasVerifiedEmail()) {
                $this->sendCode($user->id);
                Auth::logout();
                return response()->json(['message' => 'Verify your email.', 'code' => 'EMAIL_NOT_VERIFIED'], 422);
            }
            if (!$user->is_active) {
                Auth::logout(); // Log out the user
                return response()->json(['message' => 'Contact admin, your account is inactive.'], 403);
            }
            $token = $user->createToken('user_token')->plainTextToken;
            return response()->json(['message' => 'Logged in.', 'user' => $user, 'token' => $token], 200);
        }
        return response()->json(['message' => 'Invalid credentials.'], 422);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $this->sendCode($user->id);
            return response()->json(['message' => 'OTP sent.', 'user_id' => $user->id], 200);
        }
        return response()->json(['message' => 'Email not found.'], 422);
    }

    public function verifyForgotEmail(Request $request)
    {
        $request->validate(['code' => 'required|digits:6|numeric', 'user_id' => 'required|numeric']);
        $user = User::find($request->user_id);
        if ($user && $user->code == $request->code) {
            $user->update(['email_verified_at' => Carbon::now(), 'code' => null]);
            return response()->json(['message' => 'Email verified. Set new password.', 'user_id' => $user->id], 200);
        }
        return response()->json(['message' => 'Invalid verification code.'], 422);
    }

    public function resetPassword(Request $request)
{
    // Validate the incoming request
    $request->validate([
        'user_id' => 'required|numeric|exists:users,id',
        'password' => 'required|string|min:8|confirmed', // Ensure password confirmation
    ]);

    // Find the user by ID
    $user = User::find($request->user_id);

    // Check if the user exists
    if (!$user) {
        return response()->json(['message' => 'User  not found.'], 404);
    }

    // Update the user's password
    $user->password = Hash::make($request->password);
    $user->email_verified_at = Carbon::now(); // Optionally reset email verification
    $user->code = null; // Clear the OTP code
    $user->save();

    return response()->json(['message' => 'Password has been reset successfully.'], 200);
}
}
