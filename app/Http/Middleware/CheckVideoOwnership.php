<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\MediaCollection;
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
        $media_id =  $request->id;
        $media    =  MediaCollection::find($media_id);

        //Handling media not Found
         if(!$media) {
            return response()->json([
              'status'    =>   404,
              'success'   =>   FALSE,
              'message'   =>   'Media Not Found',
            ], 404);
         }

         //Checking Video Ownership
         if (Auth::user()->id !== $media->user_id) {
            
            return response()->json([
               'status'    =>   401,
               'success'   =>   FALSE,
               'message'   =>   'Unauthorized to delete this video',
            ], 401);
         }

         $request->attributes->set('media', $media);
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
