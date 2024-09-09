<?php

namespace App\Http\Controllers;

use Mail;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\SendMail;
use App\Models\UserOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\BaseResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\AuthRequest\LoginRequest;
use App\Http\Requests\AuthRequest\SignupRequest;
use App\Http\Requests\AuthRequest\SendCodeRequest;
use App\Http\Requests\AuthRequest\VerificationRequest;
use App\Http\Requests\AuthRequest\PasswordResetRequest;
use App\Http\Requests\AuthRequest\ResetPasswordRequest;
use App\Http\Requests\AuthRequest\ForgotPasswordRequest;

class AuthController extends Controller
{
    private $currentUser;

    function __construct() {
        $this->currentUser = auth('api')->user();
    }

    // User - Register
    public function register(SignupRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->all();

            $user = User::create([
                'first_name'    =>      $data['first_name'],
                'last_name'     =>      $data['last_name'],
                'email'         =>      $data['email'],
                'password'      =>      Hash::make($data['password']),
            ]);
            
            if ($user) {
                $message = REGISTRATION_SUCCESS;
                $this->sendOTP($user, $message);
                $response = User::find($user->id);
                $response['message'] = $message;
                DB::commit();
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "User Created Successfully", $response);
            } else {
                return new BaseResponse(STATUS_CODE_NOTAUTHORISED, STATUS_CODE_NOTAUTHORISED, "Failed to Register account");
            }

        } catch (Exception $e) {
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
        
    }
    // User - Login
    public function login(LoginRequest $request)
    {
        try {
            DB::beginTransaction();
            $token = auth('api')->attempt($request->only(['email', 'password']));
            if (!$token) {
                return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, "Incorrect email or password");
            }

            if ((!Auth::guard('api')->user()->is_verified)) {
                return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, "Please verify your Account.");
            }
            
            if(Auth::guard('api')->user()->status == config('constants.user.banned')) {
                return new BaseResponse(STATUS_CODE_FORBIDDEN, STATUS_CODE_FORBIDDEN, "Access Denied: Account is banned.");
            }

                $user = auth('api')->user();
                // $agent->fcm_token fcm_token= $request->;
                $user->last_login = date('Y-m-d H:i:s');
                $user->save();
         
    
            if ($user && $token) {
                DB::commit();

                $responseData = [
                    'user' => $user,
                    'token' => $token,
                ];

                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Logged in successfully.", $responseData);

            }
        } catch (Exception $e) {
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }

    // User - OTP Verification
    public function verifyCode(VerificationRequest $request)
    {
        try {
            $data           =   $request->all();
            $email          =   $data['email'];
            $verify_code    =   $data['verify_code'];

            $user = User::where('email', $email)->first();

            if ($user->userOtp->otp_attempts > 3 || $user->status == config('constants.user.banned')) {
                $user->status = config('constants.user.blocked');
                $user->save();
    
                return new BaseResponse(STATUS_CODE_NOTAUTHORISED, STATUS_CODE_NOTAUTHORISED, "Account has been blocked");
            }

            // Increment logic here...

            if($verify_code == $user->userOtp->code) {

                $user->is_verified = config('constants.user.active');
                $user->save();
                $token = auth('api')->login($user);
            
                unset($user->userOtp);

                $responseData = [
                    'user' => $user,
                    'token' => $token,
                ];

                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "OTP Verified Successfully", $responseData);
            }
            else {
                $user->userOtp->otp_attempts += 1;
                $user->userOtp->save();

                return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, "Invalid OTP Code.");
            }

        } catch (Exception $e) {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }
    // User - Send OTP Code
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            DB::beginTransaction();

            $user    = User::where('email', $request->email)->first();
            $message = FORGOT_PASSWORD;

            if ($user) {
                $this->sendOTP($user, $message);
                DB::commit();
                
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Successfully send OTP");
            } else {
                
                DB::rollBack();
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Account does not exist!");
            }
        } catch(Exception $e) {
            
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
  
    }
    // User - Reset Password (Authorization Required)
    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $this->currentUser->password = Hash::make($request->password);
            $this->currentUser->save();
            
            DB::commit();
            
            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Successfully set password", collect([]));
        } catch (Exception $e) {
            
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile . $e);
        }

    }
    // User - Logout
    public function logout()
    {
        try {
            if (auth('api')->check()) {
                // $this->currentUser->fcm_token = null;
                // $this->currentUser->save();
                auth()->guard('api')->logout();
    
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Successfully logout");
            } else {
                return new BaseResponse(STATUS_CODE_NOTAUTHORISED, STATUS_CODE_NOTAUTHORISED, "User unauthorized.");
            }
        } catch(Exception $e) {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
      
    }

    // User - Sending OTP Code via Mail.
    private function sendOTP(User $user, $message)
    {
        try {
            DB::beginTransaction();
            $digits = 4;
            $otp_code = rand(pow(10, $digits - 1), pow(10, $digits) - 1);

            UserOtp::where(['user_id' => $user->id, 'is_expired' => 0])->delete();
            UserOtp::create([
                'code' => $otp_code,
                'user_id' => $user->id,
            ]);
            DB::commit();

            $details = [
                'otp_code'  =>  $otp_code,
                'message'   =>  $message
            ];

            Mail::to($user->email)->send(new SendMail($details));
        } catch(Exception $e) {
            DB::rollback();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, $e->getMessage() . $e->getLine() . $e->getFile() . $e);
        }
    }
}
