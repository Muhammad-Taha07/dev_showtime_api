<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\BaseResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\MediaRequest\VideoLikeRequest;
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
                'status'        =>  config('constants.video.pending'),
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
                'status'        => $video->status,
                'video_url'     => $mediaItem->getUrl(),
            ];

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Video Uploaded Successfully, Waiting for admin Approval.", $response);
            
        } catch (Exception $e) {
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }

    }

    // Get All Videos
    public function getAllVideos()
    {
        try {
            $videos = Video::where('status', config('constants.video.approved'))->get();

            if(!$videos) {
            return new BaseResponse(STATUS_CODE_NOTFOUND, STATUS_CODE_NOTFOUND, 'No Videos Available');
        }

        foreach ($videos as $video) {
            $mediaItem            = $video->getFirstMedia();

            $video['video_url']   = $mediaItem->getUrl();
            $video['views_count'] = $video->views->count();

            unset($video['media'], $video['views']);
        }
        
        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Videos Fetched Successfully', $videos);

        } catch (Exception $e) {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }
    
    // Fetching Single Video | Open Video
    public function getVideo(Request $request)
    {
        try {
            $video_id = $request->id;

            // $video = Video::with('user')->find($video_id);
            $video = Video::with(['user', 'comments' => function($query) {
                $query->select('id', 'comment', 'video_id', 'user_id', 'created_at'); // Select only necessary fields
            }])->find($video_id);
          
            if(!$video) {
                return new BaseResponse(STATUS_CODE_NOTFOUND, STATUS_CODE_NOTFOUND, 'Video not found');
            }

            $mediaItem = $video->getMedia();
            $videoUrl  = $mediaItem->first()?->getUrl();

            $owner     = $video->user;

            // Creating Response data for API
            $response = [
                'id'            => $video->id,
                'title'         => $video->title,
                'description'   => $video->description,
                'video_url'     => $videoUrl,
                'views_count'   => $video->views->count(),
                'user'          => [
                    'user_id'   => $owner->id,
                    'full_name' => $owner->fullname,
                    'image'     => $owner->userDetails?->image,
                ],
                'comments'      => $video->comments->map(function($comment) {
                    return [
                        'comment_id' => $comment->id,
                        'comment'    => $comment->comment,
                        'user'       => [
                            'user_id'   => $comment->user->id,
                            'full_name' => $comment->user->fullname,
                            'image'     => $comment->user->userDetails->image,
                        ],
                        // 'created_at' => $comment->created_at->toIso8601String(),
                    ];
                }),
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

            $videos  = Video::where('user_id', $user_id)->where('status', config('constants.video.approved'))->get();

            if(!$videos) {
                return new BaseResponse(STATUS_CODE_NOTFOUND, STATUS_CODE_NOTFOUND, 'Videos not found');
            }
    
            foreach ($videos as $video) {
                $mediaItem            = $video->getFirstMedia();
                $video['video_url']   = $mediaItem->getUrl();
                $video['views_count'] = $video->views->count();

                unset($video['media'], $video['views']);
            }

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Videos Fetched Successfully', $videos);

        } catch (Exception $e) {
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }

    // Open Video to View.
    public function viewVideo(Request $request)
    {
        try {
            $video_id = $request->id;
            $this->currentUser->views()->attach($video_id);

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Video view recorded successfully");

        } catch (Exception $e) {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }

    // Deleting User's Video
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

    // Like Module
    public function toggleLikeDislike(VideoLikeRequest $request)
    {
        try {
            $video_id = $request->video_id;
            
            if($this->currentUser->likeVideos()->where('video_id', $video_id)->exists()) 
            {
                $this->currentUser->likeVideos()->detach($video_id);
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Video Unliked.");
            } else {
                $this->currentUser->likeVideos()->attach($video_id, ['rating' => $request->rating]);
                
                // Notification needed for other user.
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Video liked.");
            }

        } catch (Exception $e) {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }

}
