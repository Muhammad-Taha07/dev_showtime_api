<?php

use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Coordinate\Dimension;
use Illuminate\Support\Facades\Storage;

if(!function_exists('generateThumbnail')) {
    function generateThumbnail($videoPath)
    {
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => base_path(env('FFMPEG_BINARIES_PATH')),
            'ffprobe.binaries' => base_path(env('FFPROBE_BINARIES_PATH')),
        ]);

        $video = $ffmpeg->open($videoPath);
        $frame = $video->frame(TimeCode::fromSeconds(2));

        $thumbnailPath = dirname($videoPath) . '/thumb_' . pathinfo($videoPath, PATHINFO_FILENAME) . '.png';
        $frame->save($thumbnailPath);

        return $thumbnailPath;
    }
}