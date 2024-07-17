<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserOtp extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'code', 'user_id', 'is_expired','type'
    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }
}
