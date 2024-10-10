<?php

namespace App\Models;

use App\Models\User;
use App\Models\View;
use App\Models\Rating;
use App\Models\Comment;
use App\Models\Favorite;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MediaCollection extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;


    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'type',
        'thumbnail_url',
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

    // Check if in production environment and $value is not null
    public function getThumbnailUrlAttribute($value) {

        if (app()->environment('production') && $value) {
            return env('APP_URL') . '/' . $value;
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

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites', 'media_collection_id', 'user_id')->withTimestamps();
    }


}
