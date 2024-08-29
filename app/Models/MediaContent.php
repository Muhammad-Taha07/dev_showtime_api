<?php

namespace App\Models;

use App\Models\User;
use App\Models\View;
use App\Models\Comment;
use App\Models\Favorite;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MediaContent extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $table = 'media_collections';
    
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'thumbnail_url',
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

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites', 'media_collection_id', 'user_id')->withTimestamps();
    }

}
