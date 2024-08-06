<?php

use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Coordinate\Dimension;
use Illuminate\Support\Facades\Storage;

if(!function_exists('generateThumbnail')) {
    function generateThumbnail($videoPath)
    {

        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => base_path('storage/ffmpeg/ffmpeg/bin/ffmpeg.exe'),
            'ffprobe.binaries' => base_path('storage/ffmpeg/ffmpeg/bin/ffprobe.exe'),
        ]);

        $video = $ffmpeg->open($videoPath);
        $frame = $video->frame(TimeCode::fromSeconds(1));

        // Construct the thumbnail path in the same directory as the video
        // $thumbnailPath = dirname($videoPath) . '/thumb_' . basename($videoPath, '.' . pathinfo($videoPath, PATHINFO_EXTENSION)) . '.png';
        // $frame->save($thumbnailPath);

        // $thumbnailPath = dirname($videoPath) . '/thumb_' . basename($videoPath, '.' . $file->getClientOriginalExtension()) . '.png';
        //   $thumbnailPath = dirname($videoPath) . '/thumb_' . basename($videoPath, '.' . pathinfo($videoPath, PATHINFO_EXTENSION)) . '.png';
          $thumbnailPath = dirname($videoPath) . '/thumb_' . pathinfo($videoPath, PATHINFO_FILENAME) . '.png';

          $frame->save($thumbnailPath);

          // Resize and crop the thumbnail
        //   $ffmpeg->open($thumbnailPath)
        
        //     ->save(new \FFMpeg\Format\Video\X264(), $thumbnailPath);


        // $thumbnail->save($thumbnailPath);
    
        // $thumbnailUrl = url('medias/' . $media_id . '/thumb_' . basename($videoPath, '.' . $file->getClientOriginalExtension()) . '.png');


        return $thumbnailPath;
    }
}