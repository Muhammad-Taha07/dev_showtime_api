<?php

namespace App\Models;

use App\Models\User;
use App\Models\MediaCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class View extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'media_collection_id',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mediaCollection()
    {
        return $this->belongsTo(MediaCollection::class, 'media_collection_id');
    }
}
