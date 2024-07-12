<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function generateOtp($digits)
    {
        $otp_code = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
        return $otp_code;
    }
}
