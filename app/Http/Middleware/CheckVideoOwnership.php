<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Responses\BaseResponse;
use Symfony\Component\HttpFoundation\Response;

class CheckVideoOwnership
{
/**
 * Handle an incoming request.
 *
 * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
 */
   public function handle(Request $request, Closure $next): Response
   {
      try {
        $video_id =  $request->id;
        $video    =  Video::find($video_id);

        //Handling Video not Found
         if(!$video) {
            return response()->json([
              'status'    =>   404,
              'success'   =>   FALSE,
              'message'   =>   'Video Not Found',
            ], 404);
         }

         //Checking Video Ownership
         if (Auth::user()->id !== $video->user_id) {
            
            return response()->json([
               'status'    =>   401,
               'success'   =>   FALSE,
               'message'   =>   'Unauthorized to delete this video',
            ], 401);
         }

         $request->attributes->set('video', $video);
         return $next($request);

      } catch (Exception $e) {
           return response()->json([
              'status'    =>   500,
              'success'   =>   FALSE,
              'message'   =>   'Something went wrong',
           ], 500);
      }
   }

}
