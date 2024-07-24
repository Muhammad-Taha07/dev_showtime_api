<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    private $currentUser;

    function __construct() {
        $this->currentUser = auth('api')->user();
    }

    public function getPendingMedias(Request $request)
    {
        dd("admin Logged");
    }
}
