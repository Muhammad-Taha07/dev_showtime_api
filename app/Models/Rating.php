<?php

namespace App\Models;

use App\Models\MediaCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = ['media_collection_id', 'user_id', 'rating']; 

    public function mediaCollection()
    {
        return $this->belongsTo(MediaCollection::class);
    }

}
