<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLevelUnlock extends Model
{
    protected $fillable = ['user_id', 'game', 'level', 'unlocked_at'];
    protected $casts    = ['unlocked_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
