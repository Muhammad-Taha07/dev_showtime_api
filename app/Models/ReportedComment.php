<?php

namespace App\Models;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportedComment extends Model
{
    use HasFactory;

    protected $fillable = ['reporter_id', 'comment_id', 'comment', 'reason', 'created_at', 'updated_at'];

    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id'); // Ensure this matches the column name
    }
    
    public function reportedBy()
    {
        return $this->comment->user(); // This relationship is from the Comment model
    }

}
