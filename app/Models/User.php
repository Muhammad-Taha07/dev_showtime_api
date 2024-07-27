<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\View;
use App\Models\UserOtp;
use App\Models\Favorite;
use App\Models\MediaLike;
use App\Models\UserDetails;
use App\Models\MediaCollection;
use App\Models\ReportedComment;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;
    
    protected $guarded = [];

/**
* Get the identifier that will be stored in the JWT subject claim.
*
* @return mixed
*/
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

/**
 * Return a key value array, containing any custom claims to be added to the JWT.
 *
 * @return array
 */
    public function getJWTCustomClaims()
    {
        return [];
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token', 
        'first_name', 
        'last_name'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'fullname'
    ];

    public function getFullnameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getUserByEmail($email)
    {
        return self::where('email', $email)->first();
    }

    public function userDetails()
    {
        return $this->hasOne(UserDetails::class);
    }

    public function userOtp()
    {
        return $this->hasOne(UserOtp::class);
    }

    public function mediaCollection()
    {
        return $this->hasMany(MediaCollection::class);
    }

    public function likeMedias(): BelongsToMany
    {
        return $this->belongsToMany(MediaCollection::class,MediaLike::class,'user_id','media_collection_id')->withTimestamps();
    }

        public function mediaLikes(): HasMany
    {
        return $this->hasMany(MediaLike::class);
    }

    public function views(): BelongsToMany
    {
        return $this->belongsToMany(MediaCollection::class,View::class,'user_id','media_collection_id')->withTimestamps();
    }

    public function reportedComments()
    {
        return $this->hasMany(ReportedComment::class, 'reporter_id'); // Ensure this matches the foreign key
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(MediaCollection::class, 'favorites', 'user_id', 'media_collection_id')->withTimestamps();
    }
}
