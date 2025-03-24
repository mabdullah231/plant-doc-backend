<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->user_type == 2) { // 0 = User
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized access'], 403);
    }
}
