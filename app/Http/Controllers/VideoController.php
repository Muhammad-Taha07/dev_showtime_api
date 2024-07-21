<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\BaseResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\MediaRequest\CreateVideoRequest;

class VideoController extends Controller
{
    private $currentUser;

    function __construct() {
        $this->currentUser = auth('api')->user();
    }

    // Creating Video
    public function saveVideo(CreateVideoRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = [
                'user_id'       =>  $this->currentUser->id,
                'title'         =>  $request->title,
                'description'   =>  $request->description,
            ];
            
            // Creating the video record
            $video = Video::create($data);
            $mediaItem = $video->addMedia($request->file('video'))->toMediaCollection();

            DB::commit();

            // Creating Response data for API
            $response = [
                'user_id'       => $video->user_id,
                'title'         => $video->title,
                'description'   => $video->description,
                'video_url'     => $mediaItem->getUrl(),
            ];

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Video Uploaded Successfully", $response);
            
        } catch (Exception $e) {
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }

    }
    
    // Fetching Video with URL | For OPENING VIDEO
    public function getVideo(Request $request)
    {
        try {
            $video_id = $request->id;

            $video = Video::with('user')->find($video_id);
          
            if(!$video) {
                return new BaseResponse(STATUS_CODE_NOTFOUND, STATUS_CODE_NOTFOUND, 'Video not found');
            }

            $mediaItem = $video->getMedia();
            $videoUrl   = $mediaItem->first()->getUrl();

            // Creating Response data for API
            $response = [
                'id'            => $video->id,
                'user_id'       => $video->user_id,
                'title'         => $video->title,
                'description'   => $video->description,
                'video_url'     => $videoUrl,
                'user'          => $video->user,
            ];

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Video Fetched successfully", $response);
            
        } catch (Exception $e) {
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }

    // Current User | View All User's Videos
    public function getCurrentUserVideos(Request $request)
    {
        try {
            $user_id = $this->currentUser->id;

            $videos  = Video::where('user_id', $user_id)->get();

            if(!$videos) {
                return new BaseResponse(STATUS_CODE_NOTFOUND, STATUS_CODE_NOTFOUND, 'Videos not found');
            }
    
            foreach ($videos as $video) {
                $mediaItem          = $video->getFirstMedia();
                $video['video_url'] = $mediaItem->getUrl();

                unset($video['media']);
            }

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Videos Fetched Successfully', $videos);

        } catch (Exception $e) {
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }

    //Deleting User's Video
    public function deleteVideo(Request $request) 
    {
        try {
            DB::beginTransaction();

            $video      = $request->attributes->get('video');
            $mediaItem  = $video->getMedia()->first();

            if ($mediaItem) {
                $mediaItem->delete();
            }

            $video->delete();
            DB::commit();

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Video deleted successfully');

        } catch (Exception $e) {
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }
    
}
