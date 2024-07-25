<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

     public function handle(Request $request, Closure $next)
     {
         // Check if the user is authenticated via the API guard
         if (auth('api')->check()) {
             $user = auth('api')->user();
 
             if ($user->status == config('constants.user.banned')) {
                 auth()->guard('api')->logout();
 
                 return response()->json([
                     'message' => 'You have been banned from using the app.',
                 ], 403);
             }
         }
 
         return $next($request);
     }
}
