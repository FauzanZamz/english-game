<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerGbaLog extends Model
{
    protected $fillable = [
        'user_id', 'game', 'level_num', 'theta', 'ld', 'ld_next',
        'success', 'duration_sec', 'criteria_snapshot',
    ];

    protected $casts = [
        'criteria_snapshot' => 'array',
        'success'           => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
