<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!Auth::check()) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        $user = Auth::user();
        if($user->is_admin !== 1) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
