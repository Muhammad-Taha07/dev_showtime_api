<?php

namespace App\Models;

use App\Models\User;
use App\Models\View;
use App\Models\Comment;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MediaCollection extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'type',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function views() {
        return $this->hasMany(View::class);
    }
    
    public function getStatusAttribute($value) {

        switch($value){
            case 0:
                $value = 'pending';
                break;
            
            case 1:
                $value = 'approved';
                break;

            case 2:
                $value = 'rejected';
                break;
        }
        return $value;
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    
    public function likes()
    {
        return $this->hasMany(MediaLike::class);
    }
}
