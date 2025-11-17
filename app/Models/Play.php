<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Play extends Model
{
    protected $fillable = ['user_id','game_id','score','duration_sec'];

    public function user(){ return $this->belongsTo(User::class); }
}
