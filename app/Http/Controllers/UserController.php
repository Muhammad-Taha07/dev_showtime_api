<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\BaseResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UserRequest\UpdateProfile;

class UserController extends Controller
{
    private $currentUser;

    function __construct() {
        $this->currentUser = auth('api')->user();
    }

    // Update User Profile Function
    public function updateProfile(UpdateProfile $request)
    {
        DB::beginTransaction();
        try {
                $data = $request->except(['image']);
                $oldFilePath = $this->currentUser?->userDetails?->image;
                
                if ($request->hasFile('image')) {
                    $data['image'] = uploadImage("image", $oldFilePath, $request->file('image'));
                }
                
                $criteria = ['user_id' => $this->currentUser->id];
                $this->currentUser->userDetails()->updateOrCreate($criteria, $data);
                DB::commit();

                $this->currentUser->load('userDetails');
                $response = $this->currentUser->userDetails;

                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Profile has been updated.", $response);
            
        } catch(Exception $e) {
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }
    
    // Get User Details, Mainly Shown on Profile screen
    public function getUserDetails(User $user)
    {
        try {
            $chk = $user->load('userDetails');
            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "User details retrieved successfully.", $user);
            
        } catch (Exception $e) {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
        }   
    }
}
