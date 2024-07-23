<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Models\ReportedComment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Responses\BaseResponse;
use App\Http\Requests\MediaRequest\PostComment;
use App\Http\Requests\MediaRequest\ReportComment;

class CommentController extends Controller
{
    private $currentUser;

    function __construct() {
        $this->currentUser = auth('api')->user();
    }

    // Method to fetch Comments with User details.
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

    // Deleting Comment Method.
    public function deleteComment(Request $request)
    {
        try {
            $comment = Comment::find($request->id);

            if (!$comment) {
                return new BaseResponse(STATUS_CODE_NOTFOUND, STATUS_CODE_NOTFOUND, "Comment not found.");
            }

            if($comment->user_id == $this->currentUser->id) {
                $comment->delete();
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Comment has been removed Successfully.", $comment);
            }
            else {
                return new BaseResponse(STATUS_CODE_NOTAUTHORISED, STATUS_CODE_NOTAUTHORISED, "You're Unauthorized to perform this action.");
            }

        } catch (Exception $e) {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }

    // Reporting Comment Method for Users.
    public function reportComment(ReportComment $request)
    {
        try {
            DB::beginTransaction();

            $comment_id  = $request->comment_id;
            $comment     = Comment::where('id', $comment_id)->first();

            if(!$comment) {
                return new BaseResponse(STATUS_CODE_NOTFOUND, STATUS_CODE_NOTFOUND, "Comment not found");
            }

            // Handling case for already reporting Comment.
            $existing_report = $comment->reportedComments()
            ->where('reporter_id', $this->currentUser->id)
            ->first();

            if ($existing_report) {
                return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, "You have already reported this comment.");
            }
            
            $reporting_Data = ReportedComment::create([
                'comment_id'   =>  $comment->id,
                'reporter_id'  =>  $this->currentUser->id,
                'comment'      =>  $comment->comment,
            ]);

            // Automatic Ban user once the reporting count reaches to 3.
            if ($comment->reportedComments()->count() >= 3) {
                $user = $comment->user;
                $user->status = config('constants.user.blocked');
                $user->save();
            }

            DB::commit();
            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Comment have successfully been reported, Thanks!');

        } catch (Exception $e) {
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }
}
