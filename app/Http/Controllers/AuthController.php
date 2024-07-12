<?php

namespace App\Http\Controllers;

use Mail;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\AuthRequest\SignupRequest;
use App\Http\Requests\AuthRequest\VerificationRequest;
use App\Http\Requests\AuthRequest\PasswordResetRequest;

class AuthController extends Controller
{
    public function signUp(SignupRequest $request)
    {
        try {
            $data = $request->validated();
            $digits = 4;
            $mailing_address = [];

            $verification_code = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
            
            array_push($mailing_address, $data['email']);

            $createUser = User::create([
                'first_name'    =>      $data['first_name'],
                'last_name'     =>      $data['last_name'],
                'email'         =>      $data['email'],
                'password'      =>      Hash::make($data['password']),
                'verify_code'   =>      $verification_code,
                'reset_expiry'  =>      date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s") . " +1 day")),
            ]);

            if(!$createUser)
            {
                return response()->json([
                    'status'    =>      400,
                    'success'   =>      false,
                    'message'   =>      'Error Creating User'
                ], 400);
            }

            // $send_otp = Mail::raw(env('APP_NAME') . " Verification code is: $verification_code", function ($message) use ($emailForMailing) {
            //     $message->to($emailForMailing)
            //         ->subject('Account Verification Code - Traer')->from(env('MAIL_FROM'));
            // });

            return response()->json([
                'status'    =>  200,
                'success'   =>  true,
                'message'   =>  'User Created Successfully',
                'data'      =>  $createUser
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'    =>  500,
                'success'   =>  false,
                'message'   =>  $e->getMessage() . $e->getLine() . $e->getFile() . $e
            ], 500);
        }
        
    }

    public function verifyCode(VerificationRequest $request)
    {
        try {
            $data           = $request->validated();
            $email          =   $data['email'];
            $verify_code    =   $data['verify_code'];

            $userobj = new User();
            $user    = $userobj->getUserByEmail($email);

            if($verify_code == $user->verify_code) {
                $updateData['status']       = config('constants.user.active');
                $updateData['verify_code']  =   null;

                $updated_user = $user->update($updateData);

                if(!$updated_user) {
                    return response()->json([
                        'status'    => 400,
                        'success'   => false,
                        'message'   => 'Error verifying user'
                    ], 400);
                }

                return response()->json([
                    'status'    => 200,
                    'success'   => true,
                    'message'   => 'Success: Account Verified',
                ], 200);
            }

            return response()->json([
                'status'    =>  400,
                'success'   =>  false,
                'message'   =>  'Invalid Verification Code',
            ], 400);

        } catch (Exception $e) {
            return response()->json([
                'status'    =>  500,
                'success'   =>  false,
                'message'   =>  $e->getMessage() . $e->getLine() . $e->getFile() . $e
            ], 500);
        }
    }

    public function resetPassword(PasswordResetRequest $request)
    {
        try {
            $data = $request->validated();

            $updatedData = [
                'password' => Hash::make($data['password'])
            ];

            $updatedUser = User::where('email', $data['email'])->update($updatedData);

            return response()->json([
                'status'    => 200,
                'success'   => true,
                'message'   => 'Password reset successfully',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'    => 500,
                'success'   => false,
                'message'   => $e->getMessage() . $e->getLine() . $e->getFile() . $e
            ], 500);
        }
    }
}
