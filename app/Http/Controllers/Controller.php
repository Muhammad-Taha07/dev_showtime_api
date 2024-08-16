<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;


    // public function pushNotification($currentUser, $otherUser, $message, $notificationType = '') {

    //     $extras = [
    //         'notification_type' => $notificationType,
    //         'message' =>  $message,
    //         'sender_id' => $currentUser->id,
    //         'notify_user_type' => $currentUser->is_admin == 1 ? 'admin' : 'user',
    //         'other_user_type' => $otherUser->is_admin == 1 ? 'admin' : 'user',
    //     ];

    //     $tokens[$otherUser->id] = $otherUser->fcm_token;
    //     if ($otherUser->fcm_token) {
    //         sendPushNotification(
    //             'Showtime',
    //             $message,
    //             $tokens,
    //             $extras,
    //             true
    //         );
    //     }
    // }
}
