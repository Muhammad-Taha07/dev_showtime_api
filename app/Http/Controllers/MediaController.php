<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use FFMpeg\FFMpeg;
use App\Models\User;
use App\Models\MediaCollection;
use Illuminate\Http\Request;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Responses\BaseResponse;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\MediaRequest\MediaLikeRequest;
use App\Http\Requests\MediaRequest\VideoLikeRequest;
use App\Http\Requests\MediaRequest\CreateVideoRequest;

class MediaController extends Controller
{
    private $currentUser;

    function __construct() {
        $this->currentUser = auth('api')->user();
    }

    private function determineFileType($mimeType)
    {
        switch (true) {
            case str_starts_with($mimeType, 'video/'):
                return 'video';

            case str_starts_with($mimeType, 'audio/'):
                return 'audio';

            case str_starts_with($mimeType, 'image/'):
                return 'image';

            default:
                return 'Other';
        }
    }

    // Creating Video
    public function saveMedia(CreateVideoRequest $request)
    {
        try {
            DB::beginTransaction();

            $file         = $request->file('file');
            $mimeType     = $file->getMimeType();
            $thumbnailUrl = null;
            $type         = $this->determineFileType($mimeType);

            $data = [
                'user_id'       =>  $this->currentUser->id,
                'title'         =>  $request->title,
                'description'   =>  $request->description,
                'type'          =>  $type,
                'status'        =>  config('constants.media.pending'),
            ];
            
            // Creating the video record
            $media     = MediaCollection::create($data);
            $mediaItem = $media->addMedia($request->file('file'))->toMediaCollection();

            // Generate thumbnail
            if($type == 'video') {
                $videoPath = $mediaItem->getPath();
                $thumbnailPath  = generateThumbnail($videoPath, $media->id);
    
                $thumbnailUrl   = 'medias/' . $media->id . '/' . basename($thumbnailPath);
                $media->update(['thumbnail_url' => $thumbnailUrl]);
            }
            
            DB::commit();

            // Creating Response data for API
            $response = [
                'user_id'       => $media->user_id,
                'title'         => $media->title,
                'description'   => $media->description,
                'status'        => $media->status,
                'type'          => $media->type,
                'media_url'     => $mediaItem->getUrl(),
                'thumbnail_url' => $thumbnailUrl,
            ];

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Media Uploaded Successfully, Waiting for admin Approval.", $response);

        } catch (Exception $e) {
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }

    }

    // Get All Medias | REQUIRED: THUMBNAIL
    public function getAllMedias(Request $request)
    {
        try {
            $media_type = $request->type;
     
            $medias     = MediaCollection::where('type', $media_type)->where('status', config('constants.media.approved'))->get();

            if(!$medias) {
            return new BaseResponse(STATUS_CODE_NOTFOUND, STATUS_CODE_NOTFOUND, 'No Medias Available');
        }

        foreach ($medias as $media) {
            $mediaItem            = $media->getFirstMedia();
            
            $media['media_url']   = $mediaItem->getUrl();
            $media['views_count'] = $media->views?->count() ?? 0;
            unset($media['media'], $media['views']);
        }
        
        $sortedMedias = $medias->sortByDesc('views_count')->values();
        $shuffledMedias = $medias->shuffle()->values();
        
        $data = [
            'most_watched'  =>  $sortedMedias,
            'trending'      =>  $shuffledMedias,
        ];
        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Medias Fetched Successfully', $data);

        } catch (Exception $e) {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }
    
    // Fetching Single Media File | Open Media File
    public function getMedia(Request $request)
    {
        try {
            $media_id = $request->id;

            $media = MediaCollection::where('status', config('constants.media.approved'))->with(['user', 'comments' => function($query) {
                $query->select('id', 'comment', 'media_collection_id', 'user_id', 'created_at');
            }])->find($media_id);

          
            if(!$media) {
                return new BaseResponse(STATUS_CODE_NOTFOUND, STATUS_CODE_NOTFOUND, 'Media not found');
            }

            $mediaItem = $media->getMedia();
            $mediaUrl  = $mediaItem->first()?->getUrl();

            $owner     = $media->user;

            // Creating Response data for API
            $response = [
                'id'            => $media->id,
                'title'         => $media->title,
                'description'   => $media->description,
                'media_url'     => $mediaUrl,
                'thumbnail_url' => $media->thumbnail_url,
                'views_count'   => $media->views->count(),
                'likes_count'   => $media->likes->count(),
                'media_type'    => $media->type,
                'user'          => [
                    'user_id'   => $owner->id,
                    'full_name' => $owner->fullname,
                    'image'     => $owner->userDetails?->image,
                ],
                'comments'      => $media->comments->map(function($comment) {
                    return [
                        'comment_id' => $comment->id,
                        'comment'    => $comment->comment,
                        'user'       => [
                            'user_id'   => $comment->user->id,
                            'full_name' => $comment->user->fullname,
                            'image'     => $comment->user->userDetails?->image,
                        ],
                    ];
                }), 
                'likes'         => $media->likes->map(function($like) {
                    return [
                        'like_id'    => $like->id,
                        'rating'     => $like->rating,
                        'user'       => [
                            'user_id'   => $like->user->id,
                            'full_name' => $like->user->fullname,
                        ],
                    ];
                }),
            ];

            // if ($media->type === 'audio') {
            //     $response['no_of_times_played'] = $response['views_count'];
            //     unset($response['views_count']);
            // }

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Media Fetched successfully", $response);
            
        } catch (Exception $e) {
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }

    // Current User | View All User's Medias
    public function getCurrentUserMedias(Request $request)
    {
        try {
            $user_id    = $this->currentUser->id;
            $media_type = $request->type;

            $medias = MediaCollection::where('user_id', $user_id)->where('type', $media_type)
            ->where('status', config('constants.media.approved'))
            ->with([
                'user',
                'comments' => function($query) {
                    $query->select('id', 'comment', 'media_collection_id', 'user_id', 'created_at')
                    ->with('user:id,first_name,last_name');
                },
                'likes' => function($query) {
                    $query->select('id', 'media_collection_id', 'user_id', 'rating')
                    ->with('user:id,first_name,last_name');
                }])->get();


            if($medias->isEmpty()) {
                return new BaseResponse(STATUS_CODE_NOTFOUND, STATUS_CODE_NOTFOUND, 'Medias not found');
            }

            $responseBody = $medias->map(function($media) {
                $mediaItem  = $media->getFirstMedia();
                $mediaUrl   = $mediaItem ? $mediaItem->getUrl() : null;
                $owner      = $media->user;
    
                return [
                    'id'            => $media->id,
                    'title'         => $media->title,
                    'description'   => $media->description,
                    'media_url'     => $mediaUrl,
                    'thumbnail_url' => $media->thumbnail_url,
                    'views_count'   => $media->views->count(),
                    'media_type'    => $media->type,
                    'user'          => [
                        'user_id'   => $owner->id,
                        'full_name' => $owner->fullname,
                        'image'     => $owner->userDetails?->image,
                    ],
                    'comments'      => $media->comments->map(function($comment) {
                        return [
                            'comment_id' => $comment->id,
                            'comment'    => $comment->comment,
                            'user'       => [
                                'user_id'   => $comment->user->id,
                                'full_name' => $comment->user->fullname,
                                'image'     => $comment->user->userDetails?->image,
                            ],
                        ];
                    }),
                    'likes'         => $media->likes->map(function($like) {
                        return [
                            'like_id'    => $like->id,
                            'rating'     => $like->rating,
                            'user'       => [
                                'user_id'   => $like->user->id,
                                'full_name' => $like->user->fullname,
                                'image'     => $like->user->userDetails?->image,
                            ],
                        ];
                    }),
                ];
            });
    
            // foreach ($medias as $media) {
            //     $mediaItem            = $media->getFirstMedia();
            //     $media['media_url']   = $mediaItem->getUrl();
            //     $media['views_count'] = $media->views->count();

            //     unset($media['media'], $media['views']);
            // }

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Medias Fetched Successfully', $responseBody);

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
    public function deleteMedia(Request $request) 
    {
        try {
            DB::beginTransaction();

            $media      = $request->attributes->get('media');
            $mediaItem  = $media->getMedia()->first();

            if ($mediaItem) {
                $mediaItem->delete();
            }

            $media->delete();
            DB::commit();

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Media deleted successfully');

        } catch (Exception $e) {
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }

    // Like Module
    public function toggleLikeDislike(MediaLikeRequest $request)
    {
        try {
            $media_id = $request->media_id;

            if($this->currentUser->likeMedias()->where('media_collection_id', $media_id)->exists()) 
            {
                $this->currentUser->likeMedias()->detach($media_id);
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Media Unliked.");
            } else {
                $this->currentUser->likeMedias()->attach($media_id, ['rating' => $request->rating]);
                
                // Notification needed for other user.
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Media liked.");
            }

        } catch (Exception $e) {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }

    public function toggleFavorite(Request $request)
    {
        try {
            $media_id = $request->media_id;
            $user     = $this->currentUser;

            if ($user->favorites()->where('media_collection_id', $media_id)->exists()) 
            {
                $user->favorites()->detach($media_id);
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Media removed from favorite successfully");

            } else {
                $user->favorites()->attach($media_id);
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Media added to favorite successfully");
            }

        } catch (Exception $e) {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);        }
    }

    public function getFavoriteMedia(Request $request)
    {
        try {
            $user = $this->currentUser;
            $type = $request->type;

            $medias = MediaCollection::whereHas('favoritedBy', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('type', $type)
            ->with([
                'user',
                'comments' => function($query) {
                    $query->select('id', 'comment', 'media_collection_id', 'user_id', 'created_at')
                    ->with('user:id,first_name,last_name');
                },
                'likes' => function($query) {
                    $query->select('id', 'media_collection_id', 'user_id', 'rating')
                    ->with('user:id,first_name,last_name');
                }])->get();


            if($medias->isEmpty()) {
                return new BaseResponse(STATUS_CODE_NOTFOUND, STATUS_CODE_NOTFOUND, 'No Media in your favorites');
            }

            $responseBody = $medias->map(function($media) {
                $mediaItem  = $media->getFirstMedia();
                $mediaUrl   = $mediaItem ? $mediaItem->getUrl() : null;
                $owner      = $media->user;

                return [
                    'id'            => $media->id,
                    'title'         => $media->title,
                    'description'   => $media->description,
                    'media_url'     => $mediaUrl,
                    'views_count'   => $media->views->count(),
                    'media_type'    => $media->type,
                    'user'          => [
                        'user_id'   => $owner->id,
                        'full_name' => $owner->fullname,
                        'image'     => $owner->userDetails?->image,
                    ],
                    'comments'      => $media->comments->map(function($comment) {
                        return [
                            'comment_id' => $comment->id,
                            'comment'    => $comment->comment,
                            'user'       => [
                                'user_id'   => $comment->user->id,
                                'full_name' => $comment->user->fullname,
                                'image'     => $comment->user->userDetails?->image,
                            ],
                        ];
                    }),
                    'likes'         => $media->likes->map(function($like) {
                        return [
                            'like_id'    => $like->id,
                            'rating'     => $like->rating,
                            'user'       => [
                                'user_id'   => $like->user->id,
                                'full_name' => $like->user->fullname,
                                'image'     => $like->user->userDetails?->image,
                            ],
                        ];
                    }),
                ];
            });

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Fetched Favorite media files successfully", $responseBody);

        } catch (Exception $e) {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);        
        }
    }


}
