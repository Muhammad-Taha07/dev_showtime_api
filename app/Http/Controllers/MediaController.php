<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use FFMpeg\FFMpeg;
use App\Models\User;
use App\Models\Rating;
use Illuminate\Http\Request;
use App\Models\MediaCollection;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\BaseResponse;
use Illuminate\Support\Facades\Auth;
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
                $thumb_url_response = env('APP_URL') . '/' . $thumbnailUrl;
            } else {
                $thumb_url_response = NULL;
            }
            
            DB::commit();

            // Creating Response data for API
            $response = [
                'id'            => $media->id,
                'user_id'       => $media->user_id,
                'title'         => $media->title,
                'description'   => $media->description,
                'status'        => $media->status,
                'type'          => $media->type,
                'media_url'     => $mediaItem->getUrl(),
                'thumbnail_url' => $thumb_url_response,
            ];

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Media Uploaded Successfully, Waiting for admin Approval.", $response);

        } catch (Exception $e) {
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }

    }

    // Get All Medias
    public function getAllMedias(Request $request)
    {
        try {
            $media_type = $request->type;
     
            $medias     = MediaCollection::where('type', $media_type)->where('status', config('constants.media.approved'))->get();

            if(!$medias) {
            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'No Medias Available', $medias);
        }

        foreach ($medias as $media) {
            $mediaItem            = $media->getFirstMedia();

            // Fetch average rating
            $average_rating = $media->ratings()->avg('rating');
            $average_rating = number_format((float)$average_rating, 2, '.', '');
            
            $media['media_url']    = $mediaItem->getUrl();
            $media['views_count']  = $media->views?->count() ?? 0;
            $media['likes_count']  = $media->likes->count();
            $media['is_liked']     = $media->likes()->where('user_id', $this->currentUser->id)->exists() ? 1 : 0;
            $media['is_favorited'] = $media->favoritedBy()->where('user_id', $this->currentUser->id)->exists() ? 1 : 0;
            $media['ratings']      = $average_rating;
            unset($media['media'], $media['views']);
        }
        
        $sortedMedias   = $medias->sortByDesc('views_count')->values();
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
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Media not found', collect([]));
            }

            $mediaItem = $media->getMedia();
            $mediaUrl  = $mediaItem->first()?->getUrl();
            $owner     = $media->user;

            // Fetch average rating
            $average_rating = $media->ratings()->avg('rating');
            $average_rating = number_format((float)$average_rating, 2, '.', '');

            // Creating Response data for API
            $response = [
                'id'            => $media->id,
                'title'         => $media->title,
                'description'   => $media->description,
                'media_url'     => $mediaUrl,
                'thumbnail_url' => $media->thumbnail_url,
                'views_count'   => $media->views->count(),
                'likes_count'   => $media->likes->count(),
                'is_liked'      => $media->likes()->where('user_id', $this->currentUser->id)->exists() ? 1 : 0,
                'is_favorited'  => $media->favoritedBy()->where('user_id', $this->currentUser->id)->exists() ? 1 : 0,
                'ratings'       => $average_rating,
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
                    $query->select('id', 'media_collection_id', 'user_id')
                    ->with('user:id,first_name,last_name');
                }])->get();

            if($medias->isEmpty()) {
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'No Media files found', $medias);
            }

            $responseBody = $medias->map(function($media) {
                $mediaItem  = $media->getFirstMedia();
                $mediaUrl   = $mediaItem ? $mediaItem->getUrl() : null;
                $owner      = $media->user;

                // Fetch average rating
                $average_rating = $media->ratings()->avg('rating');
                $average_rating = number_format((float)$average_rating, 2, '.', '');
    
                return [
                    'id'            => $media->id,
                    'title'         => $media->title,
                    'description'   => $media->description,
                    'media_url'     => $mediaUrl,
                    'thumbnail_url' => $media->thumbnail_url,
                    'views_count'   => $media->views->count(),
                    'likes_count'   => $media->likes->count(),
                    'is_liked'      => $media->likes()->where('user_id', $this->currentUser->id)->exists() ? 1 : 0,
                    'is_favorited'  => $media->favoritedBy()->where('user_id', $this->currentUser->id)->exists() ? 1 : 0,    
                    'ratings'       => $average_rating,
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

    // Record a view into Video.
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

            // Delete the thumbnail file
            $thumbnailPath = str_replace(url('/'), public_path(), $media->thumbnail_url); 
            // $thumbnailPath = str_replace('/', DIRECTORY_SEPARATOR, $thumbnailPath);

            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }

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
                // $this->currentUser->likeMedias()->attach($media_id, ['rating' => $request->rating]);
                $this->currentUser->likeMedias()->attach($media_id);
                
                // Notification needed for other user.
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Media liked.");
            }

        } catch (Exception $e) {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }

    // Toggle Favorite Media File
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

    // Get Favorite Media File
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
                    $query->select('id', 'media_collection_id', 'user_id')
                    ->with('user:id,first_name,last_name');
                }])->get();


            if($medias->isEmpty()) {
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'No Media exist in favorites', $medias);
            }

            $responseBody = $medias->map(function($media) {
                $mediaItem  = $media->getFirstMedia();
                $mediaUrl   = $mediaItem ? $mediaItem->getUrl() : null;
                $owner      = $media->user;

                // Fetch average rating
                $average_rating = $media->ratings()->avg('rating');
                $average_rating = number_format((float)$average_rating, 2, '.', '');

                return [
                    'id'            => $media->id,
                    'title'         => $media->title,
                    'description'   => $media->description,
                    'media_url'     => $mediaUrl,
                    // 'thumbnail_url' => env('APP_URL') . '/' . $media->thumbnail_url,
                    'thumbnail_url' => $media->thumbnail_url,
                    'views_count'   => $media->views->count(),
                    'likes_count'   => $media->likes->count(),
                    'is_liked'      => $media->likes()->where('user_id', $this->currentUser->id)->exists() ? 1 : 0,
                    'is_favorited'  => $media->favoritedBy()->where('user_id', $this->currentUser->id)->exists() ? 1 : 0,
                    'ratings'       => $average_rating,
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
    // Rating Media
    public function rateMedia(Request $request) 
    {
        try {
            DB::beginTransaction();

            $authUser     = $this->currentUser;
            $media_id     = $request->media_id;
            $rating_value = (float) $request->rating;

            $media = MediaCollection::where('id', $media_id)->first();

            if($media && $media->status == 'approved') {

                $rating = Rating::updateOrCreate([
                        'user_id' => $authUser->id,
                        'media_collection_id' => $media_id,
                    ],
                    [
                        'rating' => $rating_value
                    ]);
    
                DB::commit();
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Rating Recorded Successfully', collect([]));
            }

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Media file does not exist', collect([]));
            
        } catch (Exception $e) {
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);   
        }
    }

}
