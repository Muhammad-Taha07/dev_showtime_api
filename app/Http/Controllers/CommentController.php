<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\BaseResponse;
use App\Http\Requests\MediaRequest\PostComment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    private $currentUser;

    function __construct() {
        $this->currentUser = auth('api')->user();
    }

    public function getComments(Request $request)
    {
        try {
            $video_id = $request->id;

            $comments = Comment::where('video_id', $video_id)
            ->with(['user:id,email,first_name,last_name', 'user.userDetails:id,user_id,image'])->get();

            if($comments->isEmpty()) {
                return new BaseResponse(STATUS_CODE_NOTFOUND, STATUS_CODE_NOTFOUND, 'Comment does not exist');
            }

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Comments", $comments);

        } catch (Exception $e) {

            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }



}
