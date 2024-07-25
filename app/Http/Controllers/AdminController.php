<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MediaCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Responses\BaseResponse;
use App\Http\Requests\AdminRequest\ApproveMedia;

class AdminController extends Controller
{
    private $currentUser;

    function __construct() {
        $this->currentUser = auth('api')->user();
    }

    // Function to get Media Files according to type & Status.
    public function getMediaFiles(Request $request)
    {
        try {
            $media_type   = $request->type;
            $media_status = $request->status;

            switch($media_status) {
                case 'pending':
                    $media_status = config('constants.media.pending');
                    break;

                case 'accepted':
                    $media_status = config('constants.media.approved');
                    break;

                case 'rejected':
                    $media_status = config('constants.media.rejected');
                    break;
            }

            $medias = MediaCollection::withTrashed()->where('type', $media_type)->where('status', $media_status)->get();

            if($medias->isEmpty()) {
                return new BaseResponse(STATUS_CODE_NOTFOUND, STATUS_CODE_NOTFOUND, 'No Medias Available');
            }
            
            foreach ($medias as $media) {
                if($media->status !== 'rejected') {
                    $mediaItem            = $media->getFirstMedia();
                    $media['media_url']   = $mediaItem->getUrl();
                    unset($media['media'], $media['views']);
                }
            }

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Medias Fetched Successfully', $medias);
        
        } catch (Exception $e) {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);

        }
    }

    // Function to Approve Media Files
    public function updateMediaStatus(ApproveMedia $request)
    {
        try {
            DB::beginTransaction();

            $media_id = $request->media_id;
            $approval_status = $request->status;

            switch($approval_status) {
                case 'yes':
                    $approval_status = config('constants.media.approved');
                    break;
                
                case 'no':
                    $approval_status = config('constants.media.rejected');
                    break;
            }
            
            $media = MediaCollection::where('id', $media_id)->where('status', config('constants.media.pending'))->first();

            if(!$media) {
                return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, 'Media file not Found');
            }

            if($approval_status == config('constants.media.rejected')) {
                
                $media->status = $approval_status;
                $media->save();

                $mediaItem  = $media->getMedia()->first();

                if ($mediaItem) {
                    $mediaItem->delete();
                }

                $media->delete();
                DB::commit();

                unset($media['media']);

                //Send notification to user of the Video.
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Media File has been rejected!', $media);
            }

            $media->status = config('constants.media.approved');
            $media->save();
            DB::commit();
            
            //Send notification to user of the Video.
            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Media file approved successfully', $media);

        } catch (Exception $e) {
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }
}
