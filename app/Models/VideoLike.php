<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoLike extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'video_id', 'rating', 'created_at', 'updated_at'];
    
}
