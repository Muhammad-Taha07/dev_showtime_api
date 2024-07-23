<?php

namespace App\Models;

use App\Models\User;
use App\Models\MediaCollection;
use App\Models\ReportedComment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = ['user_id', 'media_collection_id', 'comment', 'created_at', 'updated_at'];

    public function user() 
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mediaCollection()
    {
        return $this->belongsTo(MediaCollection::class, 'media_collection_id');
    }

    public function reportedComments()
    {
        return $this->hasMany(ReportedComment::class);
    }
}
