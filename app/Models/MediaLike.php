<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaLike extends Model
{
    use HasFactory;
    
    protected $fillable = ['user_id', 'media_collection_id', 'rating', 'created_at', 'updated_at'];
}
