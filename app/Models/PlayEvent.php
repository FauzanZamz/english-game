<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayEvent extends Model
{
    protected $fillable = ['play_id','type','payload'];
}
