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

    // Method to fetch Comments with User details
    public function getComments(Request $request)
    {
        try {
            $video_id = $request->id;

            $comments = Comment::where('video_id', $video_id)
            ->with(['user:id,email,first_name,last_name', 'user.userDetails:id,user_id,image'])->get();

            if($comments->isEmpty()) {
                return new BaseResponse(STATUS_CODE_NOTFOUND, STATUS_CODE_NOTFOUND, 'Comment does not exist');
            }

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Successfully Fetched Comments", $comments);

        } catch (Exception $e) {

            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }

    // Post/Create Comments on Videos.
    public function postComment(PostComment $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();

            $createComment = Comment::create([
                'user_id'   =>  $this->currentUser->id,
                'video_id'  =>  $data['video_id'],
                'comment'   =>  $data['comment'],
            ]);

            $createComment->load([
                'user:id,email,first_name,last_name',
                'user.userDetails:id,user_id,image',
            ]);
            
            DB::commit();
            return new BaseResponse(STATUS_CODE_CREATE, STATUS_CODE_CREATE, 'Comment Added Successfully', $createComment);

        } catch (Exception $e) {
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }

    // Deleting Comment Method
    public function deleteComment(Request $request)
    {
        try {
            $comment = Comment::find($request->id);

            if (!$comment) {
                return new BaseResponse(STATUS_CODE_NOTFOUND, STATUS_CODE_NOTFOUND, "Comment not found.");
            }
            
            $comment->delete();
            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Comment has been removed Successfully.", $comment);

        } catch (Exception $e) {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }

    }
}