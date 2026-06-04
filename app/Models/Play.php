<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Play extends Model
{
    protected $fillable = ['user_id', 'game_id', 'score', 'duration_sec', 'ld_target', 'theta_result'];

    public function user(){ return $this->belongsTo(User::class); }
    public function game(){ return $this->belongsTo(Game::class); }
}
