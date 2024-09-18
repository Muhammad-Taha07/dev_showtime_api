<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Support\Facades\File;

function uploadImage(string $entity, $oldFilePath, UploadedFile $file)
{
    $image   = $file;
    $time    = time();
    $prefix  = '/uploads';
    $user_id = Auth::user()->id;
    
    if(!empty($oldFilePath) && File::exists(public_path($oldFilePath))) {
        File::delete(public_path($oldFilePath));
    }

    // Generate a unique file name with the original extension
    $image_name = $image->getClientOriginalName();
    $image_name = str_replace(' ', '_', $image_name);

    // Upload Destination
    $path = $entity . '/user_' . $user_id;
    $destination_path = public_path('/' . $prefix . '/' . $path . '');

    // Creating Folder
    if (!file_exists($destination_path)) {
        mkdir($destination_path, 0777, true);
    }

    $file_name = $time . '_' . $image_name;
    $image->move($destination_path, $file_name);
    $imageURL = $prefix . '/' . $path . '/' . $file_name;
    
    return $imageURL;

}