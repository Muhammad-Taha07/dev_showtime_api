<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'gender', 
        'image', 
        'address',
        'user_age',
        'longitude',
        'latitude',
        'bio',
        'created_at',
        'updated_at',
    ];



    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function getImageAttribute($value) {
        return env('APP_URL') . $value;
    }

}
