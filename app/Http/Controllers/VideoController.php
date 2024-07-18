<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    private $currentUser;

    function __construct() {
        $this->currentUser = auth('api')->user();
    }

    public function saveVideo(Request $request)
    {
        $data = [
            'user_id'=> $this->currentUser->id,
            'description'=>$request->description,
            'title'=>$request->title,
        ];

        Video::create($data)
        ->addMedia($request->file('video'))
        ->toMediaCollection();
        dd("done");

        // $video = Video::where('id', 1)->first();
        // $mediaItems = $video->getMedia();
        // dd($mediaItems->first()->getUrl());
        // $videoUrl = $mediaItems->first()->getUrl();
    }
    
}
